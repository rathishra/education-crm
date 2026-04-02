<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PortalUserController extends BaseController
{
    public function index(): void
    {
        $this->authorize('students.view');

        $db = $this->db;

        $search   = trim($this->input('search', ''));
        $courseId = (int)$this->input('course_id', 0);
        $batchId  = (int)$this->input('batch_id', 0);
        $access   = $this->input('access', '');   // 'enabled' | 'disabled' | 'no_password'
        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;

        // ── Build WHERE ──────────────────────────────────────────
        $where  = ['s.institution_id = ?', 's.deleted_at IS NULL'];
        $params = [$this->institutionId];

        if ($search) {
            $where[]  = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ? OR s.email LIKE ?)';
            $like     = "%{$search}%";
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($courseId) {
            $where[]  = 's.course_id = ?';
            $params[] = $courseId;
        }
        if ($batchId) {
            $where[]  = 's.batch_id = ?';
            $params[] = $batchId;
        }
        if ($access === 'enabled') {
            $where[] = 's.portal_enabled = 1';
        } elseif ($access === 'disabled') {
            $where[] = '(s.portal_enabled = 0 OR s.portal_enabled IS NULL)';
        } elseif ($access === 'no_password') {
            $where[] = '(s.password IS NULL OR s.password = "")';
        }

        $whereStr = implode(' AND ', $where);

        // ── Stats ────────────────────────────────────────────────
        $db->query(
            "SELECT
                COUNT(*)                                                    AS total,
                SUM(portal_enabled = 1)                                     AS enabled,
                SUM(portal_enabled = 0 OR portal_enabled IS NULL)           AS disabled,
                SUM(password IS NULL OR password = '')                      AS no_password,
                SUM(last_portal_login_at IS NOT NULL)                       AS ever_logged_in
             FROM students s
             WHERE {$whereStr}",
            $params
        );
        $stats = $db->fetch();

        // ── Total for pagination ─────────────────────────────────
        $db->query("SELECT COUNT(*) AS cnt FROM students s WHERE {$whereStr}", $params);
        $total = (int)($db->fetch()['cnt'] ?? 0);
        $pages = max(1, (int)ceil($total / $perPage));

        // ── Student list ─────────────────────────────────────────
        $db->query(
            "SELECT s.id, s.first_name, s.last_name, s.student_id_number, s.email,
                    s.status, s.portal_enabled,
                    (s.password IS NOT NULL AND s.password != '') AS has_password,
                    s.last_portal_login_at,
                    c.name  AS course_name,
                    b.name  AS batch_name
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             WHERE {$whereStr}
             ORDER BY s.first_name, s.last_name
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $students = $db->fetchAll();

        // ── Dropdowns ────────────────────────────────────────────
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $batches = $db->fetchAll();

        $filters  = compact('search', 'courseId', 'batchId', 'access');
        $pageTitle = 'Portal User Management';

        $this->view('students/portal-access', compact(
            'students', 'stats', 'courses', 'batches',
            'filters', 'total', 'page', 'pages', 'pageTitle'
        ));
    }

    // ── Toggle portal_enabled ────────────────────────────────────────
    public function toggleAccess(int $id): void
    {
        $this->authorize('students.edit');

        $this->db->query(
            "SELECT id, portal_enabled FROM students WHERE id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
            [$id, $this->institutionId]
        );
        $student = $this->db->fetch();

        if (!$student) {
            flash('errors', ['Student not found.']);
            redirect(url('students/portal-access'));
            return;
        }

        $newVal = $student['portal_enabled'] ? 0 : 1;
        $this->db->query(
            "UPDATE students SET portal_enabled = ? WHERE id = ?",
            [$newVal, $id]
        );

        if (isAjax()) {
            $this->json(['status' => 'ok', 'enabled' => (bool)$newVal]);
            return;
        }

        flash('success', 'Portal access ' . ($newVal ? 'enabled' : 'disabled') . ' successfully.');
        redirect(url('students/portal-access'));
    }

    // ── Set / reset password ─────────────────────────────────────────
    public function setPassword(int $id): void
    {
        $this->authorize('students.edit');

        if (!verifyCsrf()) {
            flash('errors', ['Session expired. Please try again.']);
            redirect(url('students/portal-access'));
            return;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        if (strlen($password) < 6) {
            flash('errors', ['Password must be at least 6 characters.']);
            redirect(url('students/portal-access'));
            return;
        }

        if ($password !== $confirm) {
            flash('errors', ['Passwords do not match.']);
            redirect(url('students/portal-access'));
            return;
        }

        $this->db->query(
            "SELECT id FROM students WHERE id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            flash('errors', ['Student not found.']);
            redirect(url('students/portal-access'));
            return;
        }

        $this->db->query(
            "UPDATE students SET password = ?, portal_enabled = 1 WHERE id = ?",
            [password_hash($password, PASSWORD_BCRYPT), $id]
        );

        flash('success', 'Portal password set successfully. Portal access also enabled.');
        redirect(url('students/portal-access'));
    }

    // ── Bulk actions ─────────────────────────────────────────────────
    public function bulkAction(): void
    {
        $this->authorize('students.edit');

        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('students/portal-access'));
            return;
        }

        $action = $_POST['bulk_action'] ?? '';
        $ids    = array_filter(array_map('intval', (array)($_POST['student_ids'] ?? [])));

        if (empty($ids)) {
            flash('errors', ['No students selected.']);
            redirect(url('students/portal-access'));
            return;
        }

        // Validate all IDs belong to this institution
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->query(
            "SELECT id FROM students WHERE id IN ({$placeholders}) AND institution_id = ? AND deleted_at IS NULL",
            array_merge($ids, [$this->institutionId])
        );
        $validRows = $this->db->fetchAll();
        $validIds  = array_column($validRows, 'id');

        if (empty($validIds)) {
            flash('errors', ['No valid students found.']);
            redirect(url('students/portal-access'));
            return;
        }

        $ph = implode(',', array_fill(0, count($validIds), '?'));

        switch ($action) {
            case 'enable':
                $this->db->query("UPDATE students SET portal_enabled = 1 WHERE id IN ({$ph})", $validIds);
                flash('success', count($validIds) . ' student(s) portal access enabled.');
                break;

            case 'disable':
                $this->db->query("UPDATE students SET portal_enabled = 0 WHERE id IN ({$ph})", $validIds);
                flash('success', count($validIds) . ' student(s) portal access disabled.');
                break;

            case 'set_password':
                $password = $_POST['bulk_password'] ?? '';
                if (strlen($password) < 6) {
                    flash('errors', ['Bulk password must be at least 6 characters.']);
                    redirect(url('students/portal-access'));
                    return;
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $this->db->query(
                    "UPDATE students SET password = ?, portal_enabled = 1 WHERE id IN ({$ph})",
                    array_merge([$hash], $validIds)
                );
                flash('success', count($validIds) . ' student(s) password set and portal access enabled.');
                break;

            default:
                flash('errors', ['Invalid bulk action.']);
        }

        redirect(url('students/portal-access'));
    }
}
