<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SettingController extends BaseController
{
    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function getSettings(string $group = null): array
    {
        $institutionId = $this->institutionId ?? 0;
        if ($group) {
            db()->query(
                "SELECT key_name, value FROM settings
                 WHERE institution_id = ? AND group_name = ?
                 ORDER BY key_name",
                [$institutionId, $group]
            );
        } else {
            db()->query(
                "SELECT key_name, value FROM settings
                 WHERE institution_id = ?
                 ORDER BY group_name, key_name",
                [$institutionId]
            );
        }
        $settings = [];
        foreach (db()->fetchAll() as $row) {
            $settings[$row['key_name']] = $row['value'];
        }
        return $settings;
    }

    private function saveSetting(string $key, string $value, string $group): void
    {
        $institutionId = $this->institutionId ?? 0;
        db()->query(
            "SELECT id FROM settings WHERE institution_id = ? AND key_name = ?",
            [$institutionId, $key]
        );
        $existing = db()->fetch();
        if ($existing) {
            db()->update(
                'settings',
                ['value' => $value],
                'institution_id = ? AND key_name = ?',
                [$institutionId, $key]
            );
        } else {
            db()->insert('settings', [
                'institution_id' => $institutionId,
                'key_name'       => $key,
                'value'          => $value,
                'group_name'     => $group,
            ]);
        }
    }

    private function saveGroup(array $data, string $group, array $skip = []): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $skip)) continue;
            $this->saveSetting($key, sanitize((string)$value), $group);
        }
    }

    // ─── General Settings ───────────────────────────────────────────────────────

    public function index(): void
    {
        $this->authorize('settings.view');
        $settings = $this->getSettings();
        $tab = $_GET['tab'] ?? 'general';
        $this->view('settings/index', compact('settings', 'tab'));
    }

    public function update(): void
    {
        $this->authorize('settings.edit');
        verifyCsrf();

        $group = sanitize($_POST['_group'] ?? 'general');
        $data  = $this->postData();
        unset($data['_token'], $data['_group']);

        $this->saveGroup($data, $group);
        $this->logAudit('settings_updated', 'settings', 0);

        $tab = match($group) {
            'academic'     => 'academic',
            'localization' => 'localization',
            'security'     => 'security',
            'appearance'   => 'appearance',
            default        => 'general',
        };

        $this->redirectWith(url('settings?tab=' . $tab), 'success', ucfirst($group) . ' settings saved successfully.');
    }

    // ─── Logo / Favicon Upload ──────────────────────────────────────────────────

    public function uploadLogo(): void
    {
        $this->authorize('settings.edit');
        verifyCsrf();

        $type = sanitize($_POST['logo_type'] ?? 'logo'); // 'logo' or 'favicon'
        $fileKey = $type === 'favicon' ? 'favicon_file' : 'logo_file';

        if (empty($_FILES[$fileKey]['name'])) {
            $this->redirectWith(url('settings?tab=appearance'), 'error', 'No file selected.');
            return;
        }

        $file     = $_FILES[$fileKey];
        $allowed  = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            $this->redirectWith(url('settings?tab=appearance'), 'error', 'Invalid file type. Use PNG, JPG, GIF, SVG, or ICO.');
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->redirectWith(url('settings?tab=appearance'), 'error', 'File too large. Maximum 2MB allowed.');
            return;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . ($this->institutionId ?? 0) . '_' . time() . '.' . strtolower($ext);
        $uploadDir = BASE_PATH . '/public/assets/uploads/branding/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->redirectWith(url('settings?tab=appearance'), 'error', 'Upload failed. Check folder permissions.');
            return;
        }

        $settingKey = $type === 'favicon' ? 'site_favicon' : 'site_logo';
        $this->saveSetting($settingKey, 'assets/uploads/branding/' . $filename, 'appearance');
        $this->logAudit('logo_uploaded', 'settings', 0);

        $this->redirectWith(url('settings?tab=appearance'), 'success', ucfirst($type) . ' uploaded successfully.');
    }

    // ─── Communication Settings ─────────────────────────────────────────────────

    public function communication(): void
    {
        $this->authorize('settings.view');
        $settings = $this->getSettings('communication');
        $this->view('settings/communication', compact('settings'));
    }

    public function updateCommunication(): void
    {
        $this->authorize('settings.edit');
        verifyCsrf();

        $data = $this->postData();
        unset($data['_token']);

        // Don't overwrite passwords with blank (user left field empty)
        $passwordKeys = ['sms_api_key', 'mail_password', 'whatsapp_api_key'];
        foreach ($passwordKeys as $pk) {
            if (isset($data[$pk]) && $data[$pk] === '') {
                unset($data[$pk]);
            }
        }

        $this->saveGroup($data, 'communication');
        $this->logAudit('communication_settings_updated', 'settings', 0);

        $this->redirectWith(url('settings/communication'), 'success', 'Communication settings saved successfully.');
    }

    // ─── Test Email ─────────────────────────────────────────────────────────────

    public function testEmail(): void
    {
        $this->authorize('settings.edit');

        header('Content-Type: application/json');
        $input     = json_decode(file_get_contents('php://input'), true) ?? [];
        $recipient = sanitize($input['email'] ?? ($this->user['email'] ?? ''));

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit;
        }

        $settings = $this->getSettings('communication');

        $host       = $settings['mail_host']         ?? '';
        $port       = (int)($settings['mail_port']   ?? 587);
        $username   = $settings['mail_username']     ?? '';
        $password   = $settings['mail_password']     ?? '';
        $fromAddr   = $settings['mail_from_address'] ?? $username;
        $fromName   = $settings['mail_from_name']    ?? 'CRM System';
        $encryption = $settings['mail_encryption']   ?? 'tls';

        if (empty($host) || empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'SMTP settings are incomplete. Please save your SMTP configuration first.']);
            exit;
        }

        // Basic socket connection test (no PHPMailer dependency)
        $prefix = $encryption === 'ssl' ? 'ssl://' : '';
        $errno  = 0;
        $errstr = '';
        $conn   = @fsockopen($prefix . $host, $port, $errno, $errstr, 5);

        if (!$conn) {
            echo json_encode(['success' => false, 'message' => "Cannot connect to SMTP server {$host}:{$port}. Error: {$errstr}"]);
            exit;
        }
        fclose($conn);

        // If PHPMailer is available, send a real test email
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $username;
                $mail->Password   = $password;
                $mail->SMTPSecure = $encryption;
                $mail->Port       = $port;
                $mail->setFrom($fromAddr, $fromName);
                $mail->addAddress($recipient);
                $mail->Subject = 'CRM Test Email';
                $mail->Body    = 'This is a test email from your CRM system. SMTP is configured correctly.';
                $mail->send();
                echo json_encode(['success' => true, 'message' => "Test email sent successfully to {$recipient}."]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'SMTP error: ' . $e->getMessage()]);
            }
        } else {
            // Socket connected — report success without sending
            echo json_encode(['success' => true, 'message' => "SMTP connection to {$host}:{$port} successful. (PHPMailer not installed — email not sent, but connection works.)"]);
        }
        exit;
    }

    // ─── Test SMS ───────────────────────────────────────────────────────────────

    public function testSms(): void
    {
        $this->authorize('settings.edit');

        header('Content-Type: application/json');
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');

        if (strlen($mobile) < 8) {
            echo json_encode(['success' => false, 'message' => 'Invalid mobile number.']);
            exit;
        }

        $settings = $this->getSettings('communication');
        $provider = $settings['sms_provider'] ?? '';
        $apiKey   = $settings['sms_api_key']  ?? '';
        $senderId = $settings['sms_sender_id'] ?? 'MYCRM';

        if (empty($provider) || empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'SMS provider and API key must be configured first.']);
            exit;
        }

        $message = urlencode('Test SMS from your CRM system. SMS is configured correctly.');

        if ($provider === 'msg91') {
            $url = "https://api.msg91.com/api/sendhttp.php?authkey={$apiKey}&mobiles={$mobile}&message={$message}&sender={$senderId}&route=4&country=91";
            $resp = @file_get_contents($url);
            if ($resp !== false && (str_contains($resp, 'success') || str_contains($resp, 'request_id'))) {
                echo json_encode(['success' => true, 'message' => "SMS sent via MSG91 to {$mobile}."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'MSG91 error: ' . ($resp ?: 'No response from API.')]);
            }
        } elseif ($provider === 'twilio') {
            $settings2 = $this->getSettings('communication');
            $accountSid = $settings2['twilio_account_sid'] ?? '';
            if (empty($accountSid)) {
                echo json_encode(['success' => false, 'message' => 'Twilio Account SID not configured.']);
            } else {
                echo json_encode(['success' => true, 'message' => "Twilio credentials present. SMS to {$mobile} would be sent via Twilio API."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Unknown SMS provider: {$provider}"]);
        }
        exit;
    }
}
