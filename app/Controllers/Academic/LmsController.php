<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class LmsController extends BaseController
{
    private string $uploadDir = 'public/uploads/lms/';

    public function index(): void
    {
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $batchId   = (int)($_GET['batch_id']   ?? 0);
        $type      = trim($_GET['type'] ?? '');

        $where  = "m.deleted_at IS NULL AND m.institution_id = ?";
        $params = [$this->institutionId];
        if ($subjectId) { $where .= " AND m.subject_id = ?"; $params[] = $subjectId; }
        if ($batchId)   { $where .= " AND m.batch_id = ?";   $params[] = $batchId; }
        if ($type)      { $where .= " AND m.material_type = ?"; $params[] = $type; }

        $this->db->query(
            "SELECT m.*,
                    s.subject_name, s.subject_code,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    b.program_name, b.batch_term
             FROM lms_materials m
             JOIN subjects s ON s.id = m.subject_id
             JOIN users u ON u.id = m.faculty_id
             LEFT JOIN academic_batches b ON b.id = m.batch_id
             WHERE {$where}
             ORDER BY m.created_at DESC",
            $params
        );
        $materials = $this->db->fetchAll();

        // Stats
        $this->db->query(
            "SELECT material_type, COUNT(*) AS cnt FROM lms_materials
             WHERE institution_id=? AND deleted_at IS NULL GROUP BY material_type",
            [$this->institutionId]
        );
        $statsRaw = $this->db->fetchAll();
        $stats = [];
        foreach ($statsRaw as $r) $stats[$r['material_type']] = $r['cnt'];

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id=? AND status='active' AND deleted_at IS NULL ORDER BY subject_name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id=? AND status='active' ORDER BY program_name", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->view('academic/lms/index', compact('materials', 'stats', 'subjects', 'batches', 'subjectId', 'batchId', 'type'));
    }

    public function create(): void
    {
        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id=? AND status='active' AND deleted_at IS NULL ORDER BY subject_name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id=? AND status='active' ORDER BY program_name", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->view('academic/lms/create', compact('subjects', 'batches'));
    }

    public function store(): void
    {
        verifyCsrf();

        $subjectId = (int)$this->input('subject_id');
        $title     = trim($this->input('title', ''));

        if (!$subjectId || empty($title)) {
            return $this->backWithErrors([
                'subject_id' => !$subjectId ? 'Subject is required.' : '',
                'title'      => empty($title) ? 'Title is required.' : '',
            ]);
        }

        $filePath         = null;
        $originalFilename = null;
        $fileSize         = null;
        $fileType         = null;

        // Handle file upload
        if (!empty($_FILES['file']['name'])) {
            $file    = $_FILES['file'];
            $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','zip','jpg','jpeg','png','mp4','avi','mkv'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                return $this->backWithErrors(['file' => 'File type not allowed. Allowed: ' . implode(', ', $allowed)]);
            }
            if ($file['size'] > 50 * 1024 * 1024) {
                return $this->backWithErrors(['file' => 'File too large. Max 50MB.']);
            }

            $uploadPath = BASE_PATH . '/' . $this->uploadDir;
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

            $fileName = time() . '_' . preg_replace('/[^a-z0-9._-]/i', '_', $file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                $filePath         = $this->uploadDir . $fileName;
                $originalFilename = $file['name'];
                $fileSize         = $file['size'];
                $fileType         = $file['type'];
            }
        }

        $publishDate = trim($this->input('publish_date', '')) ?: date('Y-m-d');
        $dueDate     = trim($this->input('due_date', '')) ?: null;

        $this->db->insert('lms_materials', [
            'institution_id'    => $this->institutionId,
            'subject_id'        => $subjectId,
            'faculty_id'        => $this->user['id'],
            'batch_id'          => (int)$this->input('batch_id') ?: null,
            'title'             => $title,
            'description'       => trim($this->input('description', '')) ?: null,
            'material_type'     => $this->input('material_type', 'notes'),
            'file_path'         => $filePath,
            'original_filename' => $originalFilename,
            'file_size'         => $fileSize,
            'file_type'         => $fileType,
            'video_link'        => trim($this->input('video_link', '')) ?: null,
            'external_link'     => trim($this->input('external_link', '')) ?: null,
            'publish_date'      => $publishDate,
            'due_date'          => $dueDate,
            'is_published'      => (int)(bool)$this->input('is_published', '1'),
            'unit_number'       => (int)$this->input('unit_number') ?: null,
            'tags'              => trim($this->input('tags', '')) ?: null,
        ]);

        $this->logAudit('lms_create', 'lms_materials', $this->db->lastInsertId());
        $this->redirectWith(url('academic/lms'), 'success', 'Material uploaded successfully.');
    }

    public function download(int $id): void
    {
        $this->db->query(
            "SELECT * FROM lms_materials WHERE id=? AND institution_id=? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $material = $this->db->fetch();
        if (!$material || empty($material['file_path'])) {
            $this->redirectWith(url('academic/lms'), 'error', 'File not found.');
            return;
        }

        // Increment download count
        $this->db->query("UPDATE lms_materials SET download_count = download_count + 1 WHERE id=?", [$id]);

        $path = BASE_PATH . '/' . $material['file_path'];
        if (!file_exists($path)) {
            $this->redirectWith(url('academic/lms'), 'error', 'File missing on server.');
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $material['original_filename'] . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function destroy(int $id): void
    {
        verifyCsrf();
        $this->db->query(
            "UPDATE lms_materials SET deleted_at=NOW() WHERE id=? AND institution_id=?",
            [$id, $this->institutionId]
        );
        $this->logAudit('lms_delete', 'lms_materials', $id);
        $this->redirectWith(url('academic/lms'), 'success', 'Material deleted.');
    }
}
