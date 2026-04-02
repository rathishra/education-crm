<?php
namespace App\Controllers\Portal;

class ProfileController extends PortalBaseController
{
    public function index(): void
    {
        $profile = $this->getStudentProfile();
        if (!$profile) {
            flash('error', 'Profile not found.');
            redirect(url('portal/student/dashboard'));
            return;
        }

        // Parent info
        $this->db->query("SELECT * FROM student_parents WHERE student_id = ? LIMIT 1", [$this->studentId]);
        $parents = $this->db->fetch() ?: [];

        // Documents
        $this->db->query(
            "SELECT * FROM student_documents WHERE student_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
            [$this->studentId]
        );
        $documents = $this->db->fetchAll();

        $pageTitle = 'My Profile';
        $this->view('portal/profile/index', compact('profile', 'parents', 'documents', 'pageTitle'));
    }

    public function changePassword(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('portal/student/profile'));
            return;
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Load current hash
        $this->db->query("SELECT password FROM students WHERE id = ? LIMIT 1", [$this->studentId]);
        $row = $this->db->fetch();

        if (!$row || !$row['password'] || !password_verify($current, $row['password'])) {
            flash('errors', ['Current password is incorrect.']);
            redirect(url('portal/student/profile'));
            return;
        }

        if (strlen($new) < 8) {
            flash('errors', ['New password must be at least 8 characters.']);
            redirect(url('portal/student/profile'));
            return;
        }

        if ($new !== $confirm) {
            flash('errors', ['New passwords do not match.']);
            redirect(url('portal/student/profile'));
            return;
        }

        $this->db->query(
            "UPDATE students SET password = ? WHERE id = ?",
            [password_hash($new, PASSWORD_BCRYPT), $this->studentId]
        );

        flash('success', 'Password changed successfully.');
        redirect(url('portal/student/profile'));
    }
}
