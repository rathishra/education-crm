<?php
namespace App\Controllers;

use Core\Database\Database;
use Core\Session\Session;

abstract class BaseController
{
    protected Database $db;
    protected Session $session;
    protected ?array $user;
    protected ?int $institutionId;

    public function __construct()
    {
        $this->db = db();
        $this->session = Session::getInstance();
        $this->user = auth();
        $this->institutionId = currentInstitutionId();
    }

    /**
     * Render a view with layout
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Make data available as variables
        extract($data);

        // Common variables
        $currentUser = $this->user;
        $currentInstitutionId = $this->institutionId;

        // Capture view content
        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            appLog("View not found: {$viewFile}", 'error');
            echo "View not found: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render with layout
        $layoutFile = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render view without layout (for modals, partials)
     */
    protected function partial(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }

    /**
     * JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        jsonResponse($data, $status);
    }

    /**
     * Success JSON
     */
    protected function success(string $message, array $data = [], int $status = 200): void
    {
        $this->json(array_merge(['success' => true, 'message' => $message], $data), $status);
    }

    /**
     * Error JSON
     */
    protected function error(string $message, int $status = 400, array $errors = []): void
    {
        $response = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        $this->json($response, $status);
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWith(string $url, string $type, string $message): void
    {
        flash($type, $message);
        redirect($url);
    }

    /**
     * Redirect back with errors and old input
     */
    protected function backWithErrors(array $errors, array $input = []): void
    {
        flash('errors', $errors);
        if (!empty($input)) {
            $this->session->flashInput($input);
        }
        back();
    }

    /**
     * Redirect back with generic success message
     */
    protected function backWithSuccess(string $message): void
    {
        $this->session->setFlash('success', $message);
        back();
    }

    /**
     * Get POST data
     */
    protected function input(string $key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        if ($key === null) {
            return $data;
        }
        return $data[$key] ?? $default;
    }

    /**
     * Get sanitized POST data
     */
    protected function postData(array $keys = null): array
    {
        $data = $_POST;
        unset($data['_token'], $data['_method']);

        if ($keys !== null) {
            $data = array_intersect_key($data, array_flip($keys));
        }

        return sanitize($data);
    }

    /**
     * Validate input
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            $label = ucfirst(str_replace('_', ' ', $field));

            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = $this->checkRule($rule, $field, $value, $label, $params, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break; // One error per field
                }
            }
        }

        return $errors;
    }

    private function checkRule(string $rule, string $field, $value, string $label, array $params, array $data): ?string
    {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "{$label} is required.";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$label} must be a valid email.";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$params[0]) {
                    return "{$label} must be at least {$params[0]} characters.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$params[0]) {
                    return "{$label} must not exceed {$params[0]} characters.";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "{$label} must be a number.";
                }
                break;

            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9]{10,15}$/', $value)) {
                    return "{$label} must be a valid phone number.";
                }
                break;

            case 'unique':
                // unique:table,column,except_id
                if (!empty($value)) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    $exceptId = $params[2] ?? null;

                    $where = "`{$column}` = ?";
                    $bindings = [$value];

                    if ($exceptId) {
                        $where .= " AND `id` != ?";
                        $bindings[] = $exceptId;
                    }

                    if ($this->db->exists($table, $where, $bindings)) {
                        return "{$label} already exists.";
                    }
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($data[$confirmField] ?? null)) {
                    return "{$label} confirmation does not match.";
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    return "{$label} must be one of: " . implode(', ', $params);
                }
                break;

            case 'date':
                if (!empty($value) && strtotime($value) === false) {
                    return "{$label} must be a valid date.";
                }
                break;
        }

        return null;
    }

    /**
     * Log activity
     */
    protected function logAudit(string $action, string $modelType = null, int $modelId = null, $oldValues = null, $newValues = null): void
    {
        $this->db->insert('audit_logs', [
            'user_id'        => $this->user['id'] ?? null,
            'institution_id' => $this->institutionId,
            'action'         => $action,
            'model_type'     => $modelType,
            'model_id'       => $modelId,
            'old_values'     => $oldValues ? json_encode($oldValues) : null,
            'new_values'     => $newValues ? json_encode($newValues) : null,
            'ip_address'     => getClientIp(),
            'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'url'            => $_SERVER['REQUEST_URI'] ?? null,
        ]);
    }

    /**
     * Check permission or abort
     */
    protected function authorize(string $permission): void
    {
        if (!hasPermission($permission)) {
            if (isAjax()) {
                $this->error('Unauthorized', 403);
            }
            http_response_code(403);
            $this->view('errors.403', [], 'error');
            exit;
        }
    }

    /**
     * Handle file upload
     */
    protected function uploadFile(string $inputName, string $directory = 'documents'): ?array
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$inputName];
        $maxSize = config('app.max_upload_size', 5 * 1024 * 1024);

        if ($file['size'] > $maxSize) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = config('app.allowed_extensions', []);
        if (!empty($allowed) && !in_array($ext, $allowed)) {
            return null;
        }

        $storedName = uniqid('file_', true) . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/uploads/' . $directory;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $storedName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'original_name' => $file['name'],
                'stored_name'   => $storedName,
                'file_path'     => 'uploads/' . $directory . '/' . $storedName,
                'mime_type'     => $file['type'],
                'size'          => $file['size'],
            ];
        }

        return null;
    }
}
