<?php
namespace App\Controllers\Portal;

class DocumentController extends PortalBaseController
{
    public function index(): void
    {
        $sid  = $this->studentId;
        $inst = $this->institutionId;
        $db   = $this->db;

        // From dedicated student_documents table
        $db->query(
            "SELECT *, 'student_doc' AS source
             FROM student_documents
             WHERE student_id = ? AND deleted_at IS NULL
             ORDER BY document_type, created_at DESC",
            [$sid]
        );
        $documents = $db->fetchAll();

        // Also fetch from polymorphic documents table if it exists
        try {
            $db->query(
                "SELECT *, 'document' AS source
                 FROM documents
                 WHERE documentable_type = 'student' AND documentable_id = ? AND institution_id = ?
                 ORDER BY document_type, created_at DESC",
                [$sid, $inst]
            );
            $polyDocs = $db->fetchAll();
            $documents = array_merge($documents, $polyDocs);
        } catch (\Throwable $e) {
            // Table may not exist — ignore
        }

        // Admission record (for admission letter download link)
        $db->query(
            "SELECT id, admission_number, status FROM admissions
             WHERE student_id = ? ORDER BY id DESC LIMIT 1",
            [$sid]
        );
        $admission = $db->fetch();

        // Group documents by type
        $byType = [];
        foreach ($documents as $doc) {
            $byType[$doc['document_type']][] = $doc;
        }

        $pageTitle = 'My Documents';
        $this->view('portal/documents/index', compact('documents', 'byType', 'admission', 'pageTitle'));
    }
}
