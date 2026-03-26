<?php
namespace App\Models;

class Followup extends BaseModel
{
    protected string $table = 'followups';
    // No softDeletes — followups use hard delete

    // =========================================================
    // LIST & SEARCH
    // =========================================================

    /**
     * Paginated list with filters and full JOINs.
     *
     * Supported filters:
     *   search, status, followup_mode, priority, assigned_to,
     *   enquiry_id, lead_id, student_id, date_from, date_to,
     *   only_mine (user_id), tab (today|overdue|upcoming)
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where  = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        // Full-text search across names and text fields
        if (!empty($filters['search'])) {
            $s        = '%' . $filters['search'] . '%';
            $where   .= " AND (f.subject LIKE ? OR f.description LIKE ? OR f.remarks LIKE ?"
                      . " OR CONCAT(l.first_name, ' ', l.last_name) LIKE ?"
                      . " OR e.enquiry_number LIKE ?"
                      . " OR CONCAT(e.first_name, ' ', e.last_name) LIKE ?"
                      . " OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
            $params   = array_merge($params, [$s, $s, $s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where   .= " AND f.status = ?";
            $params[] = $filters['status'];
        }

        // followup_mode is the new canonical column; also falls back to type
        if (!empty($filters['followup_mode'])) {
            $where   .= " AND (f.followup_mode = ? OR (f.followup_mode IS NULL AND f.type = ?))";
            $params   = array_merge($params, [$filters['followup_mode'], $filters['followup_mode']]);
        }

        if (!empty($filters['priority'])) {
            $where   .= " AND f.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['assigned_to'])) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['enquiry_id'])) {
            $where   .= " AND f.enquiry_id = ?";
            $params[] = $filters['enquiry_id'];
        }

        if (!empty($filters['lead_id'])) {
            $where   .= " AND f.lead_id = ?";
            $params[] = $filters['lead_id'];
        }

        if (!empty($filters['student_id'])) {
            $where   .= " AND f.student_id = ?";
            $params[] = $filters['student_id'];
        }

        if (!empty($filters['date_from'])) {
            $where   .= " AND f.followup_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where   .= " AND f.followup_date <= ?";
            $params[] = $filters['date_to'];
        }

        // Scope to requesting user's own followups
        if (!empty($filters['only_mine'])) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        // Tab-based quick filters
        $tab = $filters['tab'] ?? 'all';
        if ($tab === 'today') {
            $where .= " AND DATE(f.followup_date) = CURDATE()";
        } elseif ($tab === 'overdue') {
            $where .= " AND f.followup_date < CURDATE() AND f.status = 'pending'";
        } elseif ($tab === 'upcoming') {
            $where .= " AND f.followup_date > CURDATE() AND f.status = 'pending'";
        }

        $orderBy = "FIELD(f.status,'pending','rescheduled','missed','cancelled','completed'),"
                 . " f.followup_date ASC, f.followup_time ASC";

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       l.phone                                 AS lead_phone,
                       e.enquiry_number,
                       CONCAT(e.first_name, ' ', e.last_name) AS enquiry_name,
                       CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name,
                       CONCAT(uc.first_name,' ', uc.last_name) AS created_by_name
                FROM followups f
                LEFT JOIN leads    l   ON l.id  = f.lead_id
                LEFT JOIN enquiries e  ON e.id  = f.enquiry_id
                LEFT JOIN students  s  ON s.id  = f.student_id
                LEFT JOIN users     u  ON u.id  = f.assigned_to
                LEFT JOIN users     uc ON uc.id = f.created_by
                WHERE {$where}
                ORDER BY {$orderBy}";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    // =========================================================
    // DETAIL VIEW
    // =========================================================

    /**
     * Single followup with all related entity details and rescheduling history.
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name)   AS lead_name,
                       l.lead_number, l.phone AS lead_phone, l.email AS lead_email,
                       e.enquiry_number,
                       CONCAT(e.first_name, ' ', e.last_name)   AS enquiry_name,
                       e.phone                                   AS enquiry_phone,
                       e.email                                   AS enquiry_email,
                       CONCAT(s.first_name, ' ', s.last_name)   AS student_name,
                       s.student_id_number,
                       s.phone                                   AS student_phone,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_name,
                       ua.email                                  AS assigned_email,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name,
                       rf.followup_date                          AS rescheduled_from_date,
                       rf.followup_mode                          AS rescheduled_from_mode,
                       rf.status                                 AS rescheduled_from_status
                FROM followups f
                LEFT JOIN leads     l   ON l.id  = f.lead_id
                LEFT JOIN enquiries e   ON e.id  = f.enquiry_id
                LEFT JOIN students  s   ON s.id  = f.student_id
                LEFT JOIN users     ua  ON ua.id = f.assigned_to
                LEFT JOIN users     uc  ON uc.id = f.created_by
                LEFT JOIN followups rf  ON rf.id = f.rescheduled_from
                WHERE f.id = ?";
        $params = [$id];

        if ($this->institutionScope) {
            $sql    .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql .= " LIMIT 1";

        $this->db->query($sql, $params);
        $followup = $this->db->fetch();

        if (!$followup) {
            return null;
        }

        // Fetch rescheduling / history chain
        $this->db->query(
            "SELECT id, followup_date, followup_mode, status, response, remarks
             FROM followups
             WHERE rescheduled_from = ? OR id = ?
             ORDER BY followup_date ASC, id ASC",
            [$id, $id]
        );
        $followup['history'] = $this->db->fetchAll();

        return $followup;
    }

    // =========================================================
    // STATS
    // =========================================================

    /**
     * Aggregate stats for the follow-up dashboard.
     *
     * Returns:
     *   today, pending, overdue, completed_today, completed_this_week,
     *   rescheduled, counselor_wise
     */
    public function getStats(?int $userId = null): array
    {
        $where  = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT
                    SUM(DATE(followup_date) = CURDATE() AND status = 'pending')                              AS today,
                    SUM(status = 'pending')                                                                  AS pending,
                    SUM(followup_date < CURDATE() AND status = 'pending')                                    AS overdue,
                    SUM(DATE(followup_date) = CURDATE() AND status = 'completed')                            AS completed_today,
                    SUM(WEEK(followup_date, 1) = WEEK(NOW(), 1) AND status = 'completed')                   AS completed_this_week,
                    SUM(status = 'rescheduled')                                                              AS rescheduled
                FROM followups
                WHERE {$where}";

        $this->db->query($sql, $params);
        $row = $this->db->fetch();

        $stats = [
            'today'              => (int)($row['today']              ?? 0),
            'pending'            => (int)($row['pending']            ?? 0),
            'overdue'            => (int)($row['overdue']            ?? 0),
            'completed_today'    => (int)($row['completed_today']    ?? 0),
            'completed_this_week'=> (int)($row['completed_this_week']?? 0),
            'rescheduled'        => (int)($row['rescheduled']        ?? 0),
            'counselor_wise'     => [],
        ];

        // Counselor-wise breakdown (institution-scoped, no user filter)
        if ($this->institutionScope) {
            $stats['counselor_wise'] = $this->getCounselorWise($this->institutionScope);
        }

        return $stats;
    }

    // =========================================================
    // TODAY / OVERDUE / UPCOMING
    // =========================================================

    /**
     * All pending/rescheduled followups for today, ordered by time.
     */
    public function getTodayFollowups(?int $userId = null): array
    {
        $where  = "DATE(f.followup_date) = CURDATE() AND f.status IN ('pending','rescheduled')";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name)   AS lead_name,
                       l.lead_number,
                       e.enquiry_number,
                       CONCAT(e.first_name, ' ', e.last_name)   AS enquiry_name,
                       CONCAT(s.first_name, ' ', s.last_name)   AS student_name,
                       CONCAT(u.first_name, ' ', u.last_name)   AS assigned_name
                FROM followups f
                LEFT JOIN leads     l ON l.id = f.lead_id
                LEFT JOIN enquiries e ON e.id = f.enquiry_id
                LEFT JOIN students  s ON s.id = f.student_id
                LEFT JOIN users     u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_time ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Pending followups whose followup_date is in the past.
     */
    public function getOverdueFollowups(?int $userId = null): array
    {
        $where  = "f.followup_date < CURDATE() AND f.status = 'pending'";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name)   AS lead_name,
                       l.lead_number,
                       e.enquiry_number,
                       CONCAT(e.first_name, ' ', e.last_name)   AS enquiry_name,
                       CONCAT(s.first_name, ' ', s.last_name)   AS student_name,
                       CONCAT(u.first_name, ' ', u.last_name)   AS assigned_name
                FROM followups f
                LEFT JOIN leads     l ON l.id = f.lead_id
                LEFT JOIN enquiries e ON e.id = f.enquiry_id
                LEFT JOIN students  s ON s.id = f.student_id
                LEFT JOIN users     u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_date ASC, f.followup_time ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Pending followups within the next $days days (tomorrow inclusive).
     */
    public function getUpcomingFollowups(int $days = 7, ?int $userId = null): array
    {
        $where  = "f.followup_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
                . " AND DATE_ADD(CURDATE(), INTERVAL ? DAY)"
                . " AND f.status = 'pending'";
        $params = [$days];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name)   AS lead_name,
                       l.lead_number,
                       e.enquiry_number,
                       CONCAT(e.first_name, ' ', e.last_name)   AS enquiry_name,
                       CONCAT(s.first_name, ' ', s.last_name)   AS student_name,
                       CONCAT(u.first_name, ' ', u.last_name)   AS assigned_name
                FROM followups f
                LEFT JOIN leads     l ON l.id = f.lead_id
                LEFT JOIN enquiries e ON e.id = f.enquiry_id
                LEFT JOIN students  s ON s.id = f.student_id
                LEFT JOIN users     u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_date ASC, f.followup_time ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    // =========================================================
    // BY ENTITY
    // =========================================================

    /**
     * All followups for a specific enquiry, newest first.
     */
    public function getByEnquiry(int $enquiryId): array
    {
        $where  = "f.enquiry_id = ?";
        $params = [$enquiryId];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql = "SELECT f.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_date DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * All followups for a specific lead, newest first.
     */
    public function getByLead(int $leadId): array
    {
        $where  = "f.lead_id = ?";
        $params = [$leadId];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql = "SELECT f.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_date DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * All followups for a specific student, newest first.
     */
    public function getByStudent(int $studentId): array
    {
        $where  = "f.student_id = ?";
        $params = [$studentId];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql = "SELECT f.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.followup_date DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    // =========================================================
    // COMPLETE
    // =========================================================

    /**
     * Mark a followup as completed and optionally log a lead activity.
     */
    public function complete(int $id, string $response, string $remarks, ?int $userId = null): bool
    {
        $affected = $this->db->update(
            $this->table,
            [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'response'     => $response,
                'remarks'      => $remarks,
                'updated_by'   => $userId,
            ],
            "`id` = ?",
            [$id]
        );

        if ($affected && $userId) {
            $followup = $this->find($id);
            if ($followup && !empty($followup['lead_id'])) {
                // Map followup_mode / type to lead_activities type ENUM
                $mode = $followup['followup_mode'] ?? $followup['type'] ?? 'call';
                $activityTypeMap = [
                    'call'     => 'call',
                    'whatsapp' => 'whatsapp',
                    'email'    => 'email',
                    'meeting'  => 'meeting',
                    'visit'    => 'note',
                    'sms'      => 'sms',
                ];
                $activityType = $activityTypeMap[$mode] ?? 'note';

                $leadModel = new Lead();
                $leadModel->addActivity(
                    (int)$followup['lead_id'],
                    $activityType,
                    ucfirst($mode) . ' follow-up completed'
                        . (!empty($followup['subject']) ? ': ' . $followup['subject'] : ''),
                    $remarks ?: null,
                    $userId,
                    ['followup_id' => $id, 'response' => $response]
                );
            }
        }

        return (bool)$affected;
    }

    // =========================================================
    // RESCHEDULE
    // =========================================================

    /**
     * Mark the current followup as rescheduled and insert a new one.
     *
     * $data keys: followup_date, followup_time, followup_mode,
     *             assigned_to, priority, remarks
     *
     * Returns the new followup id.
     */
    public function reschedule(int $id, array $data, int $userId): int
    {
        $original = $this->find($id);
        if (!$original) {
            return 0;
        }

        // Mark original as rescheduled
        $this->db->update(
            $this->table,
            [
                'status'     => 'rescheduled',
                'updated_by' => $userId,
            ],
            "`id` = ?",
            [$id]
        );

        $newDate = $data['followup_date'] ?? null;
        $newTime = $data['followup_time'] ?? null;
        $mode    = $data['followup_mode'] ?? $original['followup_mode'] ?? $original['type'] ?? 'call';

        // Build scheduled_at for backward compat
        $scheduledAt = $newDate
            ? ($newDate . ' ' . ($newTime ?: '09:00:00'))
            : $original['scheduled_at'];

        $newFollowup = [
            'institution_id'  => $original['institution_id'],
            'enquiry_id'      => $original['enquiry_id']  ?: null,
            'lead_id'         => $original['lead_id']     ?: null,
            'student_id'      => $original['student_id']  ?: null,
            'assigned_to'     => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : (int)$original['assigned_to'],
            'type'            => $mode,
            'followup_mode'   => $mode,
            'subject'         => $original['subject'],
            'description'     => $original['description'],
            'scheduled_at'    => $scheduledAt,
            'followup_date'   => $newDate,
            'followup_time'   => $newTime ?: null,
            'priority'        => $data['priority'] ?? $original['priority'],
            'remarks'         => $data['remarks'] ?? null,
            'rescheduled_from'=> $id,
            'status'          => 'pending',
            'created_by'      => $userId,
        ];

        return (int)$this->db->insert($this->table, $newFollowup);
    }

    // =========================================================
    // CALENDAR EVENTS
    // =========================================================

    /**
     * Follow-up events formatted for FullCalendar JSON.
     * Uses followup_date/time columns with scheduled_at as fallback.
     */
    public function getCalendarEvents(string $start, string $end, ?int $userId = null): array
    {
        $where  = "(f.followup_date >= ? AND f.followup_date <= ?)"
                . " OR (f.followup_date IS NULL AND f.scheduled_at >= ? AND f.scheduled_at <= ?)";
        $params = [$start, $end, $start, $end];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.id, f.subject, f.type, f.followup_mode, f.status, f.priority,
                       f.followup_date, f.followup_time, f.scheduled_at, f.completed_at,
                       CONCAT(l.first_name, ' ', l.last_name)   AS lead_name,
                       CONCAT(e.first_name, ' ', e.last_name)   AS enquiry_name,
                       CONCAT(s.first_name, ' ', s.last_name)   AS student_name
                FROM followups f
                LEFT JOIN leads     l ON l.id = f.lead_id
                LEFT JOIN enquiries e ON e.id = f.enquiry_id
                LEFT JOIN students  s ON s.id = f.student_id
                WHERE {$where}
                ORDER BY f.followup_date ASC, f.followup_time ASC";

        $this->db->query($sql, $params);
        $rows = $this->db->fetchAll();

        $modeColors = [
            'call'     => '#3B82F6',
            'whatsapp' => '#22C55E',
            'email'    => '#8B5CF6',
            'visit'    => '#EF4444',
            'meeting'  => '#F59E0B',
            'sms'      => '#10B981',
            'other'    => '#6B7280',
        ];

        $events = [];
        foreach ($rows as $row) {
            // Build entity label
            $entityName = $row['lead_name'] ?? $row['enquiry_name'] ?? $row['student_name'] ?? '';
            $title      = $row['subject'] . ($entityName ? ' — ' . $entityName : '');

            $mode     = $row['followup_mode'] ?: $row['type'];
            $dateStr  = $row['followup_date'] ?? substr($row['scheduled_at'], 0, 10);
            $timeStr  = $row['followup_time']
                ? (substr($row['followup_time'], 0, 5))
                : (!empty($row['scheduled_at']) ? substr($row['scheduled_at'], 11, 5) : '09:00');
            $startDt  = $dateStr . 'T' . $timeStr;

            $events[] = [
                'id'    => (int)$row['id'],
                'title' => $title,
                'start' => $startDt,
                'color' => $modeColors[$mode] ?? $modeColors['other'],
                'url'   => url('followups/' . $row['id']),
                'extendedProps' => [
                    'mode'     => $mode,
                    'status'   => $row['status'],
                    'priority' => $row['priority'],
                ],
            ];
        }

        return $events;
    }

    // =========================================================
    // COUNSELOR-WISE SUMMARY
    // =========================================================

    /**
     * Per-counselor followup count breakdown for the given institution.
     */
    public function getCounselorWise(int $institutionId): array
    {
        $sql = "SELECT
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name)                   AS counselor_name,
                    COUNT(f.id)                                               AS total,
                    SUM(f.status = 'pending')                                 AS pending,
                    SUM(DATE(f.followup_date) = CURDATE())                    AS today,
                    SUM(f.followup_date < CURDATE() AND f.status = 'pending') AS overdue,
                    SUM(f.status = 'completed')                               AS completed
                FROM followups f
                JOIN users u ON u.id = f.assigned_to
                WHERE f.institution_id = ?
                GROUP BY u.id, u.first_name, u.last_name
                ORDER BY pending DESC, total DESC";

        $this->db->query($sql, [$institutionId]);
        return $this->db->fetchAll();
    }

    // =========================================================
    // LEGACY HELPERS (kept for backward compat)
    // =========================================================

    /**
     * Next N upcoming followups (scheduled_at >= NOW()).
     * Kept for dashboard widgets that still call this.
     */
    public function getUpcoming(int $limit = 10, ?int $userId = null): array
    {
        $where  = "f.status = 'pending' AND (f.followup_date >= CURDATE() OR (f.followup_date IS NULL AND f.scheduled_at >= NOW()))";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY COALESCE(f.followup_date, DATE(f.scheduled_at)) ASC
                LIMIT ?";
        $params[] = $limit;

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Overdue followups (status pending, date/time in past).
     * Kept for dashboard widgets that still call this.
     */
    public function getOverdue(?int $userId = null): array
    {
        $where  = "f.status = 'pending'"
                . " AND (f.followup_date < CURDATE()"
                . "   OR (f.followup_date IS NULL AND f.scheduled_at < NOW()))";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where   .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY COALESCE(f.followup_date, DATE(f.scheduled_at)) ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Count of today's followups for a specific user.
     * Kept for backward compat with old index() controller.
     */
    public function getTodayCount(int $userId): int
    {
        $where  = "(DATE(followup_date) = CURDATE() OR (followup_date IS NULL AND DATE(scheduled_at) = CURDATE()))"
                . " AND assigned_to = ?";
        $params = [$userId];

        if ($this->institutionScope) {
            $where   .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        return $this->count($where, $params);
    }
}
