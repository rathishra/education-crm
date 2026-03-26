<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CommunicationController extends BaseController
{
    public function templates(): void
    {
        $this->authorize('communication.view');

        $institutionId = session('institution_id');
        $where = "1=1";
        $params = [];
        if ($institutionId) { $where .= " AND institution_id = ?"; $params[] = $institutionId; }

        db()->query("SELECT * FROM communication_templates WHERE {$where} ORDER BY name", $params);
        $templates = db()->fetchAll();

        $this->view('communication/templates', compact('templates'));
    }

    public function storeTemplate(): void
    {
        $this->authorize('communication.create');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'content' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');
        db()->insert('communication_templates', [
            'institution_id' => $institutionId,
            'name'           => sanitize($data['name']),
            'type'           => $data['type'] ?? 'sms',
            'subject'        => sanitize($data['subject'] ?? ''),
            'content'        => $data['content'],
            'variables'      => sanitize($data['variables'] ?? ''),
            'status'         => 'active',
            'created_by'     => auth()['id'],
        ]);

        $this->redirectWith('communication/templates', 'Template created.', 'success');
    }

    public function updateTemplate(int $id): void
    {
        $this->authorize('communication.edit');

        $data = $this->postData();
        db()->update('communication_templates', [
            'name'    => sanitize($data['name'] ?? ''),
            'subject' => sanitize($data['subject'] ?? ''),
            'content' => $data['content'] ?? '',
            'status'  => $data['status'] ?? 'active',
        ], '`id` = ?', [$id]);

        $this->redirectWith('communication/templates', 'Template updated.', 'success');
    }

    public function send(): void
    {
        $this->authorize('communication.send');

        $data = $this->postData();
        $type     = $data['type'] ?? 'sms';
        $to       = $data['to'] ?? '';
        $message  = $data['message'] ?? '';
        $entityId = (int)($data['entity_id'] ?? 0);
        $entityType = $data['entity_type'] ?? '';

        if (empty($to) || empty($message)) {
            jsonResponse(['success' => false, 'message' => 'Recipient and message required.'], 422);
            return;
        }

        // Log the communication
        db()->insert('communications', [
            'institution_id'  => session('institution_id'),
            'type'            => $type,
            'recipient'       => $to,
            'message'         => $message,
            'entity_type'     => $entityType,
            'entity_id'       => $entityId ?: null,
            'status'          => 'sent',
            'sent_by'         => auth()['id'],
            'sent_at'         => date('Y-m-d H:i:s'),
        ]);

        jsonResponse(['success' => true, 'message' => ucfirst($type) . ' sent successfully.']);
    }

    public function bulkForm(): void
    {
        $this->authorize('communication.bulk');

        db()->query("SELECT id, name FROM communication_templates WHERE status = 'active' ORDER BY name");
        $templates = db()->fetchAll();

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();

        $this->view('communication/bulk', compact('templates', 'courses'));
    }

    public function sendBulk(): void
    {
        $this->authorize('communication.bulk');

        $data = $this->postData();
        $type        = $data['type'] ?? 'sms';
        $message     = $data['message'] ?? '';
        $targetGroup = $data['target_group'] ?? 'all';
        $courseId    = $data['course_id'] ?? null;

        if (empty($message)) {
            $this->backWithErrors(['Message is required.']);
            return;
        }

        $institutionId = session('institution_id');

        // Build recipient list
        $where = "s.deleted_at IS NULL AND s.status = 'active'";
        $params = [];
        if ($institutionId) { $where .= " AND s.institution_id = ?"; $params[] = $institutionId; }
        if ($courseId && $targetGroup === 'course') { $where .= " AND s.course_id = ?"; $params[] = $courseId; }

        db()->query("SELECT s.id, s.phone, s.email, CONCAT(s.first_name, ' ', s.last_name) as name FROM students s WHERE {$where}", $params);
        $recipients = db()->fetchAll();

        $campaignId = db()->insert('bulk_campaigns', [
            'institution_id' => $institutionId,
            'name'           => 'Bulk ' . ucfirst($type) . ' - ' . date('d M Y H:i'),
            'type'           => $type,
            'message'        => $message,
            'target_group'   => $targetGroup,
            'total_count'    => count($recipients),
            'status'         => 'processing',
            'created_by'     => auth()['id'],
        ]);

        $sentCount = 0;
        foreach ($recipients as $r) {
            $to = ($type === 'email') ? $r['email'] : $r['phone'];
            if (!$to) continue;

            db()->insert('communications', [
                'institution_id' => $institutionId,
                'campaign_id'    => $campaignId,
                'type'           => $type,
                'recipient'      => $to,
                'message'        => $message,
                'entity_type'    => 'student',
                'entity_id'      => $r['id'],
                'status'         => 'sent',
                'sent_by'        => auth()['id'],
                'sent_at'        => date('Y-m-d H:i:s'),
            ]);
            $sentCount++;
        }

        db()->update('bulk_campaigns', ['status' => 'completed', 'sent_count' => $sentCount], '`id` = ?', [$campaignId]);

        $this->redirectWith('communication/log', "{$sentCount} messages sent successfully.", 'success');
    }

    public function log(): void
    {
        $this->authorize('communication.view');

        $where = "1=1";
        $params = [];
        $institutionId = session('institution_id');
        if ($institutionId) { $where .= " AND c.institution_id = ?"; $params[] = $institutionId; }

        $type = $this->input('type');
        if ($type) { $where .= " AND c.type = ?"; $params[] = $type; }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as sent_by_name
                FROM communications c
                LEFT JOIN users u ON u.id = c.sent_by
                WHERE {$where}
                ORDER BY c.sent_at DESC";

        $communications = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('communication/log', compact('communications', 'type'));
    }
}
