<?php
namespace App\Controllers\Portal;

class DocumentController extends PortalBaseController
{
    private array $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    private int   $maxSize      = 5242880; // 5 MB

    public function index(): void
    {
        $sid  = $this->studentId;
        $inst = $this->institutionId;
        $db   = $this->db;

        $db->query(
            "SELECT *, 'student_doc' AS source
             FROM student_documents
             WHERE student_id = ? AND deleted_at IS NULL
             ORDER BY document_type, created_at DESC",
            [$sid]
        );
        $documents = $db->fetchAll();

        try {
            $db->query(
                "SELECT *, 'document' AS source
                 FROM documents
                 WHERE documentable_type = 'student' AND documentable_id = ? AND institution_id = ?
                 ORDER BY document_type, created_at DESC",
                [$sid, $inst]
            );
            $polyDocs  = $db->fetchAll();
            $documents = array_merge($documents, $polyDocs);
        } catch (\Throwable $e) {}

        $db->query(
            "SELECT id, admission_number, status FROM admissions
             WHERE student_id = ? ORDER BY id DESC LIMIT 1",
            [$sid]
        );
        $admission = $db->fetch();

        $byType = [];
        foreach ($documents as $doc) {
            $byType[$doc['document_type']][] = $doc;
        }

        $pageTitle = 'My Documents';
        $this->view('portal/documents/index', compact('documents', 'byType', 'admission', 'pageTitle'));
    }

    // ── Upload ───────────────────────────────────────────────────────
    public function upload(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired. Please try again.']);
            redirect(url('portal/student/documents'));
            return;
        }

        $docType = trim($_POST['document_type'] ?? 'other');
        $title   = trim($_POST['title'] ?? '');

        // File validation
        $file = $_FILES['document'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) {
            flash('errors', ['Please select a file to upload.']);
            redirect(url('portal/student/documents'));
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedTypes)) {
            flash('errors', ['File type not allowed. Accepted: PDF, JPG, PNG, DOC, DOCX.']);
            redirect(url('portal/student/documents'));
            return;
        }

        if ($file['size'] > $this->maxSize) {
            flash('errors', ['File too large. Maximum allowed size is 5 MB.']);
            redirect(url('portal/student/documents'));
            return;
        }

        // Save file
        $dir = BASE_PATH . '/public/uploads/student-documents/' . $this->studentId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $storedName = uniqid('doc_', true) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $storedName)) {
            flash('errors', ['Failed to save file. Please try again.']);
            redirect(url('portal/student/documents'));
            return;
        }

        $relPath = 'uploads/student-documents/' . $this->studentId . '/' . $storedName;
        $title   = $title ?: pathinfo($file['name'], PATHINFO_FILENAME);

        $this->db->query(
            "INSERT INTO student_documents
                (institution_id, student_id, document_type, title, file_path, file_name,
                 file_size, mime_type, uploaded_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $this->institutionId, $this->studentId,
                $docType, $title, $relPath,
                $file['name'], $file['size'], $file['type'],
                $this->studentId,
            ]
        );

        flash('success', 'Document uploaded successfully.');
        redirect(url('portal/student/documents'));
    }

    // ── Delete (soft) ────────────────────────────────────────────────
    public function delete(int $id): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('portal/student/documents'));
            return;
        }

        $this->db->query(
            "SELECT id, file_path FROM student_documents
             WHERE id = ? AND student_id = ? AND deleted_at IS NULL LIMIT 1",
            [$id, $this->studentId]
        );
        $doc = $this->db->fetch();

        if (!$doc) {
            flash('errors', ['Document not found.']);
            redirect(url('portal/student/documents'));
            return;
        }

        $this->db->query(
            "UPDATE student_documents SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );

        flash('success', 'Document removed successfully.');
        redirect(url('portal/student/documents'));
    }

    // ── Download ─────────────────────────────────────────────────────
    public function download(int $id): void
    {
        $this->db->query(
            "SELECT * FROM student_documents
             WHERE id = ? AND student_id = ? AND deleted_at IS NULL LIMIT 1",
            [$id, $this->studentId]
        );
        $doc = $this->db->fetch();

        if (!$doc) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }

        $fullPath = BASE_PATH . '/public/' . $doc['file_path'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'File not found on server.';
            return;
        }

        header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($doc['file_name']) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
