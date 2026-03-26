<?php
namespace App\Models;

class Enquiry extends BaseModel
{
    protected string $table = 'enquiries';

    /**
     * Paginated list with filters and joins
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND e.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where .= " AND (e.enquiry_number LIKE ? OR CONCAT(e.first_name,' ',COALESCE(e.last_name,'')) LIKE ? OR e.phone LIKE ? OR e.email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where .= " AND e.course_interested_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(e.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(e.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['only_mine'])) {
            $where .= " AND e.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        $sql = "SELECT e.*, c.name as course_name,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                       i.name as institution_name
                FROM enquiries e
                LEFT JOIN courses c ON c.id = e.course_interested_id
                LEFT JOIN users u ON u.id = e.assigned_to
                LEFT JOIN institutions i ON i.id = e.institution_id
                WHERE {$where}
                ORDER BY e.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single enquiry with details
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*, c.name as course_name, c.code as course_code,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                       i.name as institution_name
                FROM enquiries e
                LEFT JOIN courses c ON c.id = e.course_interested_id
                LEFT JOIN users u ON u.id = e.assigned_to
                LEFT JOIN institutions i ON i.id = e.institution_id
                WHERE e.id = ?";
        $this->db->query($sql, [$id]);
        return $this->db->fetch();
    }

    /**
     * Generate unique enquiry number
     */
    public function generateEnquiryNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';
        return generateNumber('ENQ', $instCode);
    }

    /**
     * Convert enquiry to lead
     */
    public function convertToLead(int $enquiryId): ?int
    {
        $enquiry = $this->find($enquiryId);
        if (!$enquiry) return null;

        $leadModel = new Lead();
        $leadModel->setInstitutionScope($enquiry['institution_id']);

        $leadData = [
            'institution_id'      => $enquiry['institution_id'],
            'lead_number'         => $leadModel->generateLeadNumber($enquiry['institution_id']),
            'first_name'          => $enquiry['first_name'],
            'last_name'           => $enquiry['last_name'] ?? null,
            'phone'               => $enquiry['phone'],
            'email'               => $enquiry['email'] ?? null,
            'course_interested_id'=> $enquiry['course_interested_id'],
            'lead_status_id'      => $leadModel->getDefaultStatusId(),
            'priority'            => 'medium',
            'notes'               => $enquiry['message'] ?? null,
            'assigned_to'         => $enquiry['assigned_to'] ?? null,
            'enquiry_id'          => $enquiryId,
        ];

        $leadId = (int)$this->db->insert('leads', $leadData);

        // Update enquiry status
        $this->update($enquiryId, [
            'status'  => 'converted',
            'lead_id' => $leadId,
        ]);

        return $leadId;
    }
}
