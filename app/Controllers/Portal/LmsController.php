<?php
namespace App\Controllers\Portal;

class LmsController extends PortalBaseController
{
    public function index(): void
    {
        $batchId = $this->getStudentBatchId();
        $inst    = $this->institutionId;
        $db      = $this->db;

        $materials = [];
        if ($batchId) {
            $db->query(
                "SELECT lm.*,
                        sub.subject_name, sub.subject_code,
                        CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) AS faculty_name
                 FROM lms_materials lm
                 JOIN subjects sub ON sub.id = lm.subject_id
                 JOIN users u      ON u.id   = lm.faculty_id
                 WHERE lm.batch_id = ? AND lm.institution_id = ?
                   AND lm.is_published = 1 AND lm.deleted_at IS NULL
                 ORDER BY lm.material_type, lm.created_at DESC",
                [$batchId, $inst]
            );
            $materials = $db->fetchAll();
        }

        // Group by subject
        $bySubject = [];
        foreach ($materials as $m) {
            $bySubject[$m['subject_name']][] = $m;
        }

        // Group by type for stats
        $byType = [];
        foreach ($materials as $m) {
            $t = $m['material_type'] ?? 'other';
            $byType[$t] = ($byType[$t] ?? 0) + 1;
        }

        $pageTitle = 'Course Materials';
        $this->view('portal/lms/index', compact('materials', 'bySubject', 'byType', 'pageTitle'));
    }

    public function download(int $id): void
    {
        $batchId = $this->getStudentBatchId();
        if (!$batchId) {
            flash('errors', ['Access denied.']);
            redirect(url('portal/student/lms'));
            return;
        }

        $this->db->query(
            "SELECT * FROM lms_materials
             WHERE id = ? AND batch_id = ? AND is_published = 1 AND deleted_at IS NULL LIMIT 1",
            [$id, $batchId]
        );
        $material = $this->db->fetch();

        if (!$material) {
            flash('errors', ['Material not found or not accessible.']);
            redirect(url('portal/student/lms'));
            return;
        }

        // Increment download count
        $this->db->query(
            "UPDATE lms_materials SET download_count = download_count + 1 WHERE id = ?",
            [$id]
        );

        $filePath = BASE_PATH . '/public/' . ltrim($material['file_path'], '/');
        if (!file_exists($filePath)) {
            flash('errors', ['File not found on server.']);
            redirect(url('portal/student/lms'));
            return;
        }

        $fileName = $material['file_name'] ?? basename($filePath);
        $mimeType = $material['mime_type'] ?? 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
}
