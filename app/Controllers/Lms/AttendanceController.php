<?php
namespace App\Controllers\Lms;

class AttendanceController extends LmsBaseController
{
    // ── Session list ──────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('attendance.view');

        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;
        $courseId = (int)$this->input('course', 0);
        $month    = $this->input('month', date('Y-m'));  // e.g. "2025-09"

        [$start, $end] = $this->_monthRange($month);

        if ($this->isLearner()) {
            [$sessions, $total] = $this->_learnerSessions($courseId, $start, $end, $perPage, $offset);
        } else {
            [$sessions, $total] = $this->_instructorSessions($courseId, $start, $end, $perPage, $offset);
        }

        $totalPages = (int)ceil($total / $perPage) ?: 1;
        $myCourses  = $this->_getMyCourses();
        $pageTitle  = 'Attendance';
        $this->view('lms/attendance/index', compact(
            'sessions', 'total', 'page', 'totalPages',
            'courseId', 'month', 'myCourses', 'pageTitle'
        ), 'main');
    }

    // ── Create session form ───────────────────────────────────
    public function create(): void
    {
        $this->authorize('attendance.manage');
        $courses   = $this->_getMyCourses();
        $courseId  = (int)$this->input('course_id', 0);
        $pageTitle = 'New Attendance Session';
        $this->view('lms/attendance/form', compact('courses', 'courseId', 'pageTitle'), 'main');
    }

    // ── Store session ─────────────────────────────────────────
    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('attendance.manage');
        $data   = $_POST;
        $errors = $this->_validateSession($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $id = (int)$this->db->insert('lms_attendance_sessions', [
                'course_id'      => (int)$data['course_id'],
                'institution_id' => $this->institutionId,
                'created_by'     => $this->lmsUserId,
                'title'          => trim($data['title']),
                'session_date'   => $data['session_date'],
                'start_time'     => !empty($data['start_time']) ? $data['start_time'] : null,
                'end_time'       => !empty($data['end_time'])   ? $data['end_time']   : null,
                'type'           => $data['type'] ?? 'offline',
                'notes'          => trim($data['notes'] ?? ''),
            ]);

            // Pre-populate absent records for all active enrollments
            $this->_seedAbsentRecords($id, (int)$data['course_id']);

            $this->audit('attendance.session_created', 'attendance_session', $id, ['title' => $data['title']]);
            flash('success', 'Session created. Now mark attendance.');
            redirect(url("elms/attendance/{$id}/mark"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create session.']);
            back();
        }
    }

    // ── Edit form ─────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('attendance.manage');
        $session   = $this->_findSession($id, true);
        $courses   = $this->_getMyCourses();
        $pageTitle = 'Edit Session';
        $this->view('lms/attendance/form', compact('session', 'courses', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('attendance.manage');
        $session = $this->_findSession($id, true);
        if ($session['is_locked']) { flash('errors', ['Session is locked.']); back(); return; }

        $data   = $_POST;
        $errors = $this->_validateSession($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $this->db->query(
                "UPDATE lms_attendance_sessions SET title=?,session_date=?,start_time=?,end_time=?,type=?,notes=?
                 WHERE id=? AND institution_id=?",
                [
                    trim($data['title']),
                    $data['session_date'],
                    !empty($data['start_time']) ? $data['start_time'] : null,
                    !empty($data['end_time'])   ? $data['end_time']   : null,
                    $data['type'] ?? 'offline',
                    trim($data['notes'] ?? ''),
                    $id, $this->institutionId,
                ]
            );
            flash('success', 'Session updated.');
            redirect(url("elms/attendance/{$id}/mark"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update session.']);
            back();
        }
    }

    // ── Delete ────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('attendance.manage');
        $this->_findSession($id, true);
        try {
            $this->db->query("DELETE FROM lms_attendance_sessions WHERE id=? AND institution_id=?", [$id, $this->institutionId]);
            flash('success', 'Session deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/attendance'));
    }

    // ── Mark attendance page ──────────────────────────────────
    public function mark(int $id): void
    {
        $this->authorize('attendance.manage');
        $session = $this->_findSession($id, true);

        // Load all active enrollments + existing records
        try {
            $this->db->query(
                "SELECT u.id AS lms_user_id,
                        CONCAT(u.first_name,' ',u.last_name) AS student_name,
                        u.email, u.avatar,
                        COALESCE(r.status, 'absent')  AS status,
                        r.notes AS rec_notes
                 FROM lms_enrollments e
                 JOIN lms_users u ON u.id = e.lms_user_id
                 LEFT JOIN lms_attendance_records r
                    ON r.session_id=? AND r.lms_user_id=u.id
                 WHERE e.course_id=? AND e.status='active'
                 ORDER BY student_name",
                [$id, $session['course_id']]
            );
            $students = $this->db->fetchAll();
        } catch (\Throwable $e) { $students = []; }

        $stats = $this->_sessionStats($students);
        $pageTitle = 'Mark Attendance — ' . $session['title'];
        $this->view('lms/attendance/mark', compact('session', 'students', 'stats', 'pageTitle'), 'main');
    }

    // ── Save attendance (AJAX or form POST) ──────────────────
    public function saveAttendance(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('attendance.manage');
        $session = $this->_findSession($id, true);
        if ($session['is_locked']) { $this->json(['error' => 'Session is locked'], 403); return; }

        $records = (array)$this->input('records', []);  // [{lms_user_id, status, notes}]
        $saved   = 0;

        try {
            foreach ($records as $rec) {
                $uid    = (int)($rec['lms_user_id'] ?? 0);
                $status = in_array($rec['status'] ?? '', ['present','absent','late','excused'])
                            ? $rec['status'] : 'absent';
                $notes  = trim($rec['notes'] ?? '');
                if (!$uid) continue;

                $this->db->query(
                    "INSERT INTO lms_attendance_records
                        (session_id, lms_user_id, status, marked_by, notes)
                     VALUES (?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                        status=VALUES(status), marked_by=VALUES(marked_by), notes=VALUES(notes)",
                    [$id, $uid, $status, $this->lmsUserId, $notes ?: null]
                );
                $saved++;
            }

            // Lock session if requested
            if (!empty($this->input('lock', ''))) {
                $this->db->query("UPDATE lms_attendance_sessions SET is_locked=1 WHERE id=?", [$id]);
            }

            $this->audit('attendance.marked', 'attendance_session', $id, ['count' => $saved]);

            if ($this->isAjax()) {
                $this->json(['status' => 'ok', 'saved' => $saved]);
                return;
            }
            flash('success', "Attendance saved for {$saved} student(s).");
            redirect(url("elms/attendance/{$id}/mark"));
        } catch (\Throwable $e) {
            if ($this->isAjax()) { $this->json(['error' => 'Failed'], 500); return; }
            flash('errors', ['Failed to save attendance.']);
            back();
        }
    }

    // ── Toggle session lock ───────────────────────────────────
    public function toggleLock(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('attendance.manage');
        $session = $this->_findSession($id, true);
        $newVal  = $session['is_locked'] ? 0 : 1;
        try {
            $this->db->query("UPDATE lms_attendance_sessions SET is_locked=? WHERE id=?", [$newVal, $id]);
            $this->json(['status' => 'ok', 'locked' => $newVal]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Report ────────────────────────────────────────────────
    public function report(): void
    {
        $this->authorize('attendance.view');
        $courseId  = (int)$this->input('course', 0);
        $month     = $this->input('month', date('Y-m'));
        $threshold = max(1, min(100, (int)$this->input('threshold', 75)));
        [$start, $end] = $this->_monthRange($month);
        $myCourses = $this->_getMyCourses();

        $summary = [];
        if ($courseId) {
            try {
                // Sessions in range for this course
                $this->db->query(
                    "SELECT s.id, s.title, s.session_date, s.type, s.is_locked,
                            COUNT(DISTINCT CASE WHEN r.status='present' THEN r.id END)  AS present_cnt,
                            COUNT(DISTINCT CASE WHEN r.status='late'    THEN r.id END)  AS late_cnt,
                            COUNT(DISTINCT CASE WHEN r.status='absent'  THEN r.id END)  AS absent_cnt,
                            COUNT(DISTINCT CASE WHEN r.status='excused' THEN r.id END)  AS excused_cnt,
                            COUNT(DISTINCT r.lms_user_id) AS marked_cnt
                     FROM lms_attendance_sessions s
                     LEFT JOIN lms_attendance_records r ON r.session_id=s.id
                     WHERE s.course_id=? AND s.institution_id=?
                       AND s.session_date BETWEEN ? AND ?
                     GROUP BY s.id ORDER BY s.session_date",
                    [$courseId, $this->institutionId, $start, $end]
                );
                $sessionStats = $this->db->fetchAll();

                // Per-student summary
                $this->db->query(
                    "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.email,
                            COUNT(DISTINCT s.id) AS total_sessions,
                            COUNT(DISTINCT CASE WHEN r.status='present'  THEN r.id END) AS present,
                            COUNT(DISTINCT CASE WHEN r.status='late'     THEN r.id END) AS late,
                            COUNT(DISTINCT CASE WHEN r.status='excused'  THEN r.id END) AS excused,
                            COUNT(DISTINCT CASE WHEN r.status='absent'   THEN r.id END) AS absent
                     FROM lms_enrollments e
                     JOIN lms_users u ON u.id=e.lms_user_id
                     JOIN lms_attendance_sessions s ON s.course_id=e.course_id
                        AND s.institution_id=? AND s.session_date BETWEEN ? AND ?
                     LEFT JOIN lms_attendance_records r
                        ON r.session_id=s.id AND r.lms_user_id=u.id
                     WHERE e.course_id=? AND e.status='active'
                     GROUP BY u.id ORDER BY student_name",
                    [$this->institutionId, $start, $end, $courseId]
                );
                $studentStats = $this->db->fetchAll();

                // Compute attendance %
                foreach ($studentStats as &$st) {
                    $attended = $st['present'] + $st['late'] + $st['excused'];
                    $st['pct'] = $st['total_sessions'] > 0
                        ? round($attended / $st['total_sessions'] * 100, 1) : 0;
                    $st['below_threshold'] = $st['pct'] < $threshold;
                }
                unset($st);

                $summary = compact('sessionStats', 'studentStats');
            } catch (\Throwable $e) { $summary = []; }
        }

        $pageTitle = 'Attendance Report';
        $this->view('lms/attendance/report', compact(
            'myCourses', 'courseId', 'month', 'threshold', 'summary', 'pageTitle'
        ), 'main');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findSession(int $id, bool $instructorOnly = false): array
    {
        try {
            $this->db->query(
                "SELECT s.*, c.title AS course_title
                 FROM lms_attendance_sessions s
                 JOIN lms_courses c ON c.id=s.course_id
                 WHERE s.id=? AND s.institution_id=?",
                [$id, $this->institutionId]
            );
            $s = $this->db->fetch();
        } catch (\Throwable $e) { $s = null; }

        if (!$s) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Session Not Found'], 'main');
            exit;
        }
        if ($instructorOnly && $this->isLearner()) {
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied'], 'main');
            exit;
        }
        return $s;
    }

    private function _seedAbsentRecords(int $sessionId, int $courseId): void
    {
        try {
            $this->db->query(
                "SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active'",
                [$courseId]
            );
            foreach ($this->db->fetchAll() as $e) {
                $this->db->query(
                    "INSERT IGNORE INTO lms_attendance_records (session_id, lms_user_id, status, marked_by)
                     VALUES (?,?,'absent',?)",
                    [$sessionId, $e['lms_user_id'], $this->lmsUserId]
                );
            }
        } catch (\Throwable $e) {}
    }

    private function _sessionStats(array $students): array
    {
        $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => count($students)];
        foreach ($students as $s) { $stats[$s['status']] = ($stats[$s['status']] ?? 0) + 1; }
        $stats['pct'] = $stats['total'] > 0
            ? round(($stats['present'] + $stats['late'] + $stats['excused']) / $stats['total'] * 100, 1) : 0;
        return $stats;
    }

    private function _getMyCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id={$this->institutionId}"
                : "instructor_id={$this->lmsUserId} AND institution_id={$this->institutionId}";
            $this->db->query("SELECT id,title FROM lms_courses WHERE {$scope} AND deleted_at IS NULL ORDER BY title");
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _monthRange(string $month): array
    {
        try {
            $dt    = new \DateTime($month . '-01');
            $start = $dt->format('Y-m-01');
            $end   = $dt->format('Y-m-t');
        } catch (\Throwable $e) {
            $start = date('Y-m-01');
            $end   = date('Y-m-t');
        }
        return [$start, $end];
    }

    private function _instructorSessions(int $courseId, string $start, string $end, int $perPage, int $offset): array
    {
        $where  = ['s.institution_id=?', "s.session_date BETWEEN ? AND ?"];
        $params = [$this->institutionId, $start, $end];
        if (!$this->isAdmin()) { $where[] = 's.created_by=?'; $params[] = $this->lmsUserId; }
        if ($courseId)         { $where[] = 's.course_id=?';  $params[] = $courseId; }
        $w = implode(' AND ', $where);
        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_attendance_sessions s WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT s.*, c.title AS course_title,
                        (SELECT COUNT(*) FROM lms_attendance_records WHERE session_id=s.id AND status='present') AS present_cnt,
                        (SELECT COUNT(*) FROM lms_attendance_records WHERE session_id=s.id AND status='absent')  AS absent_cnt,
                        (SELECT COUNT(*) FROM lms_attendance_records WHERE session_id=s.id AND status='late')    AS late_cnt,
                        (SELECT COUNT(*) FROM lms_attendance_records WHERE session_id=s.id)                      AS total_marked
                 FROM lms_attendance_sessions s
                 JOIN lms_courses c ON c.id=s.course_id
                 WHERE {$w} ORDER BY s.session_date DESC LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _learnerSessions(int $courseId, string $start, string $end, int $perPage, int $offset): array
    {
        $where  = ['s.institution_id=?','e.lms_user_id=?',"s.session_date BETWEEN ? AND ?"];
        $params = [$this->institutionId, $this->lmsUserId, $start, $end];
        if ($courseId) { $where[] = 's.course_id=?'; $params[] = $courseId; }
        $w = implode(' AND ', $where);
        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_attendance_sessions s JOIN lms_enrollments e ON e.course_id=s.course_id WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT s.*, c.title AS course_title,
                        COALESCE(r.status, 'not_marked') AS my_status
                 FROM lms_attendance_sessions s
                 JOIN lms_courses c ON c.id=s.course_id
                 JOIN lms_enrollments e ON e.course_id=s.course_id
                 LEFT JOIN lms_attendance_records r ON r.session_id=s.id AND r.lms_user_id=?
                 WHERE {$w} ORDER BY s.session_date DESC LIMIT ? OFFSET ?",
                array_merge([$this->lmsUserId], $params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _validateSession(array $d): array
    {
        $errors = [];
        if (empty(trim($d['title'] ?? '')))  $errors['title']        = 'Title required.';
        if (empty($d['course_id'] ?? ''))    $errors['course_id']    = 'Please select a course.';
        if (empty($d['session_date'] ?? '')) $errors['session_date'] = 'Session date required.';
        return $errors;
    }
}
