<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DocumentController extends BaseController
{
    public function upload(): void
    {
        $this->authorize('documents.upload');

        $data = $this->postData();
        $entityType = $data['entity_type'] ?? '';
        $entityId   = (int)($data['entity_id'] ?? 0);
        $docType    = $data['document_type'] ?? 'other';

        if (empty($_FILES['document']['name'])) {
            $this->error('No file uploaded.', 422);
            return;
        }

        // Use the input name string as per BaseController::uploadFile
        $uploaded = $this->uploadFile('document', 'documents/' . $entityType);

        if (!$uploaded) {
            $this->error('File upload failed.', 500);
            return;
        }

        $id = $this->db->insert('documents', [
            'institution_id'    => $this->institutionId,
            'documentable_type' => $entityType,
            'documentable_id'   => $entityId,
            'document_type'     => $docType,
            'title'             => sanitize($data['title'] ?? $uploaded['original_name']),
            'file_path'         => $uploaded['file_path'],
            'file_name'         => $uploaded['original_name'],
            'file_size'         => $uploaded['size'],
            'mime_type'         => $uploaded['mime_type'],
            'is_verified'       => 0,
            'uploaded_by'       => $this->user['id'],
        ]);

        $this->logAudit('document_uploaded', 'document', $id);
        $this->success('File uploaded successfully.', ['document_id' => $id]);
    }

    public function verify(int $id): void
    {
        $this->authorize('documents.verify');

        $this->db->update('documents', [
            'is_verified' => 1,
            'verified_by' => $this->user['id'],
            'verified_at' => date('Y-m-d H:i:s'),
        ], '`id` = ?', [$id]);

        $this->logAudit('document_verified', 'document', $id);
        $this->success('Document verified.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('documents.delete');

        $this->db->query("SELECT * FROM documents WHERE id = ?", [$id]);
        $doc = $this->db->fetch();

        if ($doc) {
            $fullPath = BASE_PATH . '/public/' . $doc['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            $this->db->query("DELETE FROM documents WHERE id = ?", [$id]);
            $this->logAudit('document_deleted', 'document', $id);
        }

        $this->success('Document deleted.');
    }

    public function download(int $id): void
    {
        $this->db->query("SELECT * FROM documents WHERE id = ?", [$id]);
        $doc = $this->db->fetch();

        if (!$doc) {
            http_response_code(404);
            echo 'File not found.';
            return;
        }

        $fullPath = BASE_PATH . '/public/' . $doc['file_path'];

        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'File not found on disk.';
            return;
        }

        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
    }
}
