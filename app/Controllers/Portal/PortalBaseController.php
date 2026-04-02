<?php
namespace App\Controllers\Portal;

use Core\Database\Database;
use Core\Session\Session;

abstract class PortalBaseController
{
    protected Database $db;
    protected Session  $session;
    protected ?array   $student;
    protected ?int     $studentId;
    protected ?int     $institutionId;

    public function __construct()
    {
        $this->db            = db();
        $this->session       = Session::getInstance();
        $this->student       = $this->session->get('student_portal_user');
        $this->studentId     = $this->student['id'] ?? null;
        $this->institutionId = $this->student['institution_id'] ?? null;
    }

    // ----------------------------------------------------------------
    // View rendering  (defaults to 'portal' layout)
    // ----------------------------------------------------------------

    protected function view(string $view, array $data = [], string $layout = 'portal'): void
    {
        extract($data);
        $portalStudent = $this->student;

        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            echo "View not found: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $status = 200): void
    {
        jsonResponse($data, $status);
    }

    protected function redirectWith(string $url, string $type, string $message): void
    {
        flash($type, $message);
        redirect($url);
    }

    protected function backWithErrors(array $errors): void
    {
        flash('errors', $errors);
        back();
    }

    protected function input(string $key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        return $key === null ? $data : ($data[$key] ?? $default);
    }

    protected function postData(): array
    {
        $data = $_POST;
        unset($data['_token'], $data['_method']);
        return $data;
    }

    // ----------------------------------------------------------------
    // Student profile helper (fetches full profile for display)
    // ----------------------------------------------------------------

    protected function getStudentProfile(): ?array
    {
        if (!$this->studentId) return null;

        $this->db->query(
            "SELECT s.*,
                    c.name   AS course_name,  c.code   AS course_code,
                    b.name   AS batch_name,
                    d.name   AS department_name,
                    i.name   AS institution_name,
                    sec.section_name
             FROM students s
             LEFT JOIN courses      c   ON c.id = s.course_id
             LEFT JOIN batches      b   ON b.id = s.batch_id
             LEFT JOIN departments  d   ON d.id = s.department_id
             LEFT JOIN institutions i   ON i.id = s.institution_id
             LEFT JOIN student_section_enrollments sse
                    ON sse.student_id = s.id AND sse.status = 'active'
             LEFT JOIN academic_sections sec ON sec.id = sse.section_id
             WHERE s.id = ? AND s.deleted_at IS NULL
             LIMIT 1",
            [$this->studentId]
        );
        return $this->db->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Get student's active batch_id from enterprise enrollment
    // ----------------------------------------------------------------

    protected function getStudentBatchId(): ?int
    {
        if (!$this->studentId) return null;

        $this->db->query(
            "SELECT batch_id FROM student_section_enrollments
             WHERE student_id = ? AND status = 'active'
             ORDER BY created_at DESC LIMIT 1",
            [$this->studentId]
        );
        $row = $this->db->fetch();
        return $row ? (int)$row['batch_id'] : ((int)($this->student['batch_id'] ?? 0) ?: null);
    }

    protected function getSectionId(): ?int
    {
        if (!$this->studentId) return null;

        $this->db->query(
            "SELECT section_id FROM student_section_enrollments
             WHERE student_id = ? AND status = 'active'
             ORDER BY created_at DESC LIMIT 1",
            [$this->studentId]
        );
        $row = $this->db->fetch();
        return $row ? (int)$row['section_id'] : null;
    }

    // ----------------------------------------------------------------
    // Unread notification count for layout badge
    // ----------------------------------------------------------------

    protected function getUnreadNotifCount(): int
    {
        if (!$this->studentId) return 0;
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM notifications
             WHERE student_id = ? AND is_read = 0",
            [$this->studentId]
        );
        $row = $this->db->fetch();
        return (int)($row['cnt'] ?? 0);
    }
}
