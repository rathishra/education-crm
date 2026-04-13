<?php
namespace App\Controllers\Lms;

class GradebookController extends LmsBaseController
{
    // ── Grade scale ───────────────────────────────────────────
    // Assessment type mapping: LMS → Academic
    private const ASSESSMENT_TYPE = 'lms_combined';

    private const SCALE = [
        ['min' => 90, 'letter' => 'A',  'gpa' => 4.0],
        ['min' => 85, 'letter' => 'A-', 'gpa' => 3.7],
        ['min' => 80, 'letter' => 'B+', 'gpa' => 3.3],
        ['min' => 75, 'letter' => 'B',  'gpa' => 3.0],
        ['min' => 70, 'letter' => 'B-', 'gpa' => 2.7],
        ['min' => 65, 'letter' => 'C+', 'gpa' => 2.3],
        ['min' => 60, 'letter' => 'C',  'gpa' => 2.0],
        ['min' => 55, 'letter' => 'C-', 'gpa' => 1.7],
        ['min' => 50, 'letter' => 'D',  'gpa' => 1.0],
        ['min' =>  0, 'letter' => 'F',  'gpa' => 0.0],
    ];

    // ── Instructor: course gradebook ──────────────────────────
    public function index(): void
    {
        $this->authorize('gradebook.view');

        $courseId = (int)$this->input('course', 0);
        $sort     = $this->input('sort', 'name');  // name | grade | asc | desc
        $myCourses = $this->_getMyCourses();

        $students     = [];
        $assignments  = [];
        $quizzes      = [];
        $weights      = ['assignments_pct' => 40, 'quizzes_pct' => 40, 'attendance_pct' => 20];
        $courseTitle  = '';
        $course       = null;

        if ($courseId) {
            try {
                $this->db->query("SELECT id, subject_id, title FROM lms_courses WHERE id = ? AND deleted_at IS NULL", [$courseId]);
                $course = $this->db->fetch();
            } catch (\Throwable $e) {}
            $weights     = $this->_getWeights($courseId);
            $assignments = $this->_getCourseAssignments($courseId);
            $quizzes     = $this->_getCourseQuizzes($courseId);
            $students    = $this->_buildGradebook($courseId, $assignments, $quizzes, $weights);
            $courseTitle = $this->_getCourseTitle($courseId);

            // Sort
            usort($students, function($a, $b) use ($sort) {
                return match($sort) {
                    'grade_asc'  => $a['final'] <=> $b['final'],
                    'grade_desc' => $b['final'] <=> $a['final'],
                    default      => strcmp($a['name'], $b['name']),
                };
            });
        }

        $pageTitle = 'Gradebook';
        $this->view('lms/gradebook/index', compact(
            'myCourses', 'courseId', 'courseTitle', 'students',
            'assignments', 'quizzes', 'weights', 'sort', 'pageTitle', 'course'
        ), 'main');
    }

    // ── Student drill-down ────────────────────────────────────
    public function student(int $courseId, int $userId): void
    {
        $this->authorize('gradebook.view');

        $weights     = $this->_getWeights($courseId);
        $courseTitle = $this->_getCourseTitle($courseId);

        // Student info
        try {
            $this->db->query(
                "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email
                 FROM lms_users u
                 JOIN lms_enrollments e ON e.lms_user_id=u.id
                 WHERE u.id=? AND e.course_id=? AND u.institution_id=?",
                [$userId, $courseId, $this->institutionId]
            );
            $student = $this->db->fetch();
        } catch (\Throwable $e) { $student = null; }

        if (!$student) {
            flash('errors', ['Student not found.']); redirect(url("elms/gradebook?course={$courseId}")); return;
        }

        // Assignment submissions
        try {
            $this->db->query(
                "SELECT a.id, a.title, a.max_score, a.due_at,
                        sub.score, sub.graded_at, sub.is_late,
                        sub.feedback
                 FROM lms_assignments a
                 LEFT JOIN lms_assignment_submissions sub
                    ON sub.assignment_id=a.id AND sub.lms_user_id=?
                 WHERE a.course_id=? AND a.deleted_at IS NULL
                 ORDER BY a.due_at",
                [$userId, $courseId]
            );
            $assignRows = $this->db->fetchAll();
        } catch (\Throwable $e) { $assignRows = []; }

        // Quiz best attempts
        try {
            $this->db->query(
                "SELECT q.id, q.title, q.pass_percentage, q.attempts_allowed,
                        MAX(qa.percentage) AS best_pct,
                        MAX(qa.passed)     AS ever_passed,
                        COUNT(qa.id)       AS attempts_used
                 FROM lms_quizzes q
                 LEFT JOIN lms_quiz_attempts qa
                    ON qa.quiz_id=q.id AND qa.lms_user_id=? AND qa.status='submitted'
                 WHERE q.course_id=? AND q.deleted_at IS NULL
                 GROUP BY q.id ORDER BY q.created_at",
                [$userId, $courseId]
            );
            $quizRows = $this->db->fetchAll();
        } catch (\Throwable $e) { $quizRows = []; }

        // Attendance summary
        try {
            $this->db->query(
                "SELECT
                    COUNT(DISTINCT s.id) AS total_sessions,
                    COUNT(DISTINCT CASE WHEN r.status IN('present','late','excused') THEN r.id END) AS attended
                 FROM lms_attendance_sessions s
                 LEFT JOIN lms_attendance_records r ON r.session_id=s.id AND r.lms_user_id=?
                 WHERE s.course_id=? AND s.institution_id=?",
                [$userId, $courseId, $this->institutionId]
            );
            $attRow = $this->db->fetch();
        } catch (\Throwable $e) { $attRow = ['total_sessions' => 0, 'attended' => 0]; }

        $attPct = $attRow['total_sessions'] > 0
            ? round($attRow['attended'] / $attRow['total_sessions'] * 100, 1) : null;

        // Compute averages
        $assignAvg = $this->_avg(array_filter(array_map(fn($r) =>
            $r['max_score'] > 0 ? round($r['score'] / $r['max_score'] * 100, 1) : null, $assignRows)));
        $quizAvg   = $this->_avg(array_filter(array_map(fn($r) =>
            $r['best_pct'] !== null ? (float)$r['best_pct'] : null, $quizRows)));

        $final     = $this->_calcFinal($assignAvg, $quizAvg, $attPct, $weights);
        $letter    = $this->_letter($final);

        // Check override
        try {
            $this->db->query(
                "SELECT * FROM lms_grade_overrides WHERE course_id=? AND lms_user_id=?",
                [$courseId, $userId]
            );
            $override = $this->db->fetch() ?: null;
        } catch (\Throwable $e) { $override = null; }

        $pageTitle = 'Grades — ' . $student['name'];
        $this->view('lms/gradebook/student', compact(
            'student', 'courseId', 'courseTitle', 'weights',
            'assignRows', 'quizRows', 'attRow', 'attPct',
            'assignAvg', 'quizAvg', 'final', 'letter', 'override', 'pageTitle'
        ), 'main');
    }

    // ── Learner: my grades ────────────────────────────────────
    public function myGrades(): void
    {
        $this->authorize('gradebook.view');

        try {
            $this->db->query(
                "SELECT e.course_id, c.title AS course_title, c.thumbnail,
                        e.progress_pct, e.enrolled_at
                 FROM lms_enrollments e
                 JOIN lms_courses c ON c.id=e.course_id
                 WHERE e.lms_user_id=? AND e.status='active' AND c.deleted_at IS NULL
                 ORDER BY c.title",
                [$this->lmsUserId]
            );
            $enrollments = $this->db->fetchAll();
        } catch (\Throwable $e) { $enrollments = []; }

        $courseGrades = [];
        foreach ($enrollments as $en) {
            $cid     = (int)$en['course_id'];
            $weights = $this->_getWeights($cid);

            // Assignment avg
            try {
                $this->db->query(
                    "SELECT AVG(sub.score / a.max_score * 100) AS avg_pct
                     FROM lms_assignments a
                     JOIN lms_assignment_submissions sub ON sub.assignment_id=a.id AND sub.lms_user_id=?
                     WHERE a.course_id=? AND a.deleted_at IS NULL AND a.max_score>0 AND sub.score IS NOT NULL",
                    [$this->lmsUserId, $cid]
                );
                $assignAvg = $this->db->fetch()['avg_pct'];
                $assignAvg = $assignAvg !== null ? round((float)$assignAvg, 1) : null;
            } catch (\Throwable $e) { $assignAvg = null; }

            // Quiz avg
            try {
                $this->db->query(
                    "SELECT AVG(best.pct) AS avg_pct FROM (
                        SELECT MAX(percentage) AS pct
                        FROM lms_quiz_attempts
                        WHERE lms_user_id=? AND status='submitted'
                          AND quiz_id IN (SELECT id FROM lms_quizzes WHERE course_id=? AND deleted_at IS NULL)
                        GROUP BY quiz_id
                    ) best",
                    [$this->lmsUserId, $cid]
                );
                $quizAvg = $this->db->fetch()['avg_pct'];
                $quizAvg = $quizAvg !== null ? round((float)$quizAvg, 1) : null;
            } catch (\Throwable $e) { $quizAvg = null; }

            // Attendance
            try {
                $this->db->query(
                    "SELECT COUNT(DISTINCT s.id) AS total,
                            COUNT(DISTINCT CASE WHEN r.status IN('present','late','excused') THEN r.id END) AS attended
                     FROM lms_attendance_sessions s
                     LEFT JOIN lms_attendance_records r ON r.session_id=s.id AND r.lms_user_id=?
                     WHERE s.course_id=?",
                    [$this->lmsUserId, $cid]
                );
                $a   = $this->db->fetch();
                $att = $a['total'] > 0 ? round($a['attended'] / $a['total'] * 100, 1) : null;
            } catch (\Throwable $e) { $att = null; }

            $final  = $this->_calcFinal($assignAvg, $quizAvg, $att, $weights);
            $letter = $this->_letter($final);

            // Override?
            try {
                $this->db->query(
                    "SELECT final_grade, letter_grade FROM lms_grade_overrides WHERE course_id=? AND lms_user_id=?",
                    [$cid, $this->lmsUserId]
                );
                $ov = $this->db->fetch() ?: null;
            } catch (\Throwable $e) { $ov = null; }

            if ($ov) { $final = (float)$ov['final_grade']; $letter = $ov['letter_grade'] ?? $this->_letter($final); }

            $courseGrades[] = array_merge($en, compact('assignAvg','quizAvg','att','final','letter','weights'));
        }

        $pageTitle = 'My Grades';
        $this->view('lms/gradebook/my_grades', compact('courseGrades', 'pageTitle'), 'main');
    }

    // ── Save grade weights (AJAX) ─────────────────────────────
    public function saveWeights(int $courseId): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('gradebook.manage');
        $a = max(0, min(100, (int)$this->input('assignments_pct', 40)));
        $q = max(0, min(100, (int)$this->input('quizzes_pct',     40)));
        $t = max(0, min(100, (int)$this->input('attendance_pct',  20)));

        if ($a + $q + $t !== 100) {
            $this->json(['error' => 'Weights must sum to 100%'], 400); return;
        }

        try {
            $this->db->query(
                "INSERT INTO lms_grade_weights (course_id, institution_id, assignments_pct, quizzes_pct, attendance_pct)
                 VALUES (?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE assignments_pct=VALUES(assignments_pct),
                     quizzes_pct=VALUES(quizzes_pct), attendance_pct=VALUES(attendance_pct)",
                [$courseId, $this->institutionId, $a, $q, $t]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Grade override (AJAX) ─────────────────────────────────
    public function override(int $courseId, int $userId): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('gradebook.manage');
        $grade  = min(100, max(0, (float)$this->input('final_grade', 0)));
        $letter = trim($this->input('letter_grade', '')) ?: $this->_letter($grade);
        $note   = trim($this->input('note', ''));

        try {
            $this->db->query(
                "INSERT INTO lms_grade_overrides
                    (course_id, lms_user_id, institution_id, final_grade, letter_grade, override_note, overridden_by)
                 VALUES (?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    final_grade=VALUES(final_grade), letter_grade=VALUES(letter_grade),
                    override_note=VALUES(override_note), overridden_by=VALUES(overridden_by)",
                [$courseId, $userId, $this->institutionId, $grade, $letter, $note ?: null, $this->lmsUserId]
            );
            $this->json(['status' => 'ok', 'final_grade' => $grade, 'letter_grade' => $letter]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Clear override (AJAX) ─────────────────────────────────
    public function clearOverride(int $courseId, int $userId): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('gradebook.manage');
        try {
            $this->db->query(
                "DELETE FROM lms_grade_overrides WHERE course_id=? AND lms_user_id=?",
                [$courseId, $userId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── CSV Export ────────────────────────────────────────────
    public function export(int $courseId): void
    {
        $this->authorize('gradebook.view');
        $weights     = $this->_getWeights($courseId);
        $assignments = $this->_getCourseAssignments($courseId);
        $quizzes     = $this->_getCourseQuizzes($courseId);
        $students    = $this->_buildGradebook($courseId, $assignments, $quizzes, $weights);
        $title       = $this->_getCourseTitle($courseId);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="gradebook_' . preg_replace('/[^a-z0-9]/i','_',$title) . '_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache');

        $f = fopen('php://output', 'w');
        fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

        // Header row
        $cols = ['Student', 'Email'];
        foreach ($assignments as $a) $cols[] = 'Assign: ' . $a['title'];
        foreach ($quizzes as $q)     $cols[] = 'Quiz: '   . $q['title'];
        $cols = array_merge($cols, ['Assign Avg %','Quiz Avg %','Attend %','Weighted %','Letter']);
        fputcsv($f, $cols);

        foreach ($students as $st) {
            $row = [$st['name'], $st['email']];
            foreach ($assignments as $a) {
                $s = $st['assignment_scores'][$a['id']] ?? null;
                $row[] = $s !== null ? number_format($s, 1) . '%' : '-';
            }
            foreach ($quizzes as $q) {
                $s = $st['quiz_scores'][$q['id']] ?? null;
                $row[] = $s !== null ? number_format($s, 1) . '%' : '-';
            }
            $row[] = $st['assign_avg']  !== null ? number_format($st['assign_avg'],  1) . '%' : '-';
            $row[] = $st['quiz_avg']    !== null ? number_format($st['quiz_avg'],    1) . '%' : '-';
            $row[] = $st['att_pct']     !== null ? number_format($st['att_pct'],     1) . '%' : '-';
            $row[] = $st['final']       !== null ? number_format($st['final'],       1) . '%' : '-';
            $row[] = $st['letter'] ?? '-';
            fputcsv($f, $row);
        }
        fclose($f);
        exit;
    }

    // ── Private helpers ───────────────────────────────────────

    private function _buildGradebook(int $courseId, array $assignments, array $quizzes, array $weights): array
    {
        // All enrolled students
        try {
            $this->db->query(
                "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email
                 FROM lms_enrollments e
                 JOIN lms_users u ON u.id=e.lms_user_id
                 WHERE e.course_id=? AND e.status='active'
                 ORDER BY name",
                [$courseId]
            );
            $rows = $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }

        // Bulk load all assignment scores
        $assignScores = [];
        if (!empty($assignments)) {
            $aIds = implode(',', array_column($assignments, 'id'));
            try {
                $this->db->query(
                    "SELECT assignment_id, lms_user_id,
                            ROUND(score / max_score * 100, 1) AS pct
                     FROM lms_assignment_submissions sub
                     JOIN lms_assignments a ON a.id=sub.assignment_id
                     WHERE sub.assignment_id IN ({$aIds}) AND a.max_score>0 AND sub.score IS NOT NULL"
                );
                foreach ($this->db->fetchAll() as $r) {
                    $assignScores[$r['lms_user_id']][$r['assignment_id']] = (float)$r['pct'];
                }
            } catch (\Throwable $e) {}
        }

        // Bulk load best quiz attempts
        $quizScores = [];
        if (!empty($quizzes)) {
            $qIds = implode(',', array_column($quizzes, 'id'));
            try {
                $this->db->query(
                    "SELECT quiz_id, lms_user_id, MAX(percentage) AS best_pct
                     FROM lms_quiz_attempts
                     WHERE quiz_id IN ({$qIds}) AND status='submitted'
                     GROUP BY quiz_id, lms_user_id"
                );
                foreach ($this->db->fetchAll() as $r) {
                    $quizScores[$r['lms_user_id']][$r['quiz_id']] = round((float)$r['best_pct'], 1);
                }
            } catch (\Throwable $e) {}
        }

        // Bulk load attendance
        $attMap = [];
        try {
            $this->db->query(
                "SELECT r.lms_user_id,
                        COUNT(DISTINCT s.id) AS total,
                        COUNT(DISTINCT CASE WHEN r.status IN('present','late','excused') THEN r.id END) AS attended
                 FROM lms_attendance_sessions s
                 LEFT JOIN lms_attendance_records r ON r.session_id=s.id
                 WHERE s.course_id=? AND s.institution_id=?
                 GROUP BY r.lms_user_id",
                [$courseId, $this->institutionId]
            );
            foreach ($this->db->fetchAll() as $r) {
                $attMap[$r['lms_user_id']] = $r['total'] > 0
                    ? round($r['attended'] / $r['total'] * 100, 1) : null;
            }
        } catch (\Throwable $e) {}

        // Check overrides
        $overrides = [];
        try {
            $this->db->query(
                "SELECT lms_user_id, final_grade, letter_grade
                 FROM lms_grade_overrides WHERE course_id=?",
                [$courseId]
            );
            foreach ($this->db->fetchAll() as $r) {
                $overrides[$r['lms_user_id']] = $r;
            }
        } catch (\Throwable $e) {}

        $students = [];
        foreach ($rows as $st) {
            $uid  = (int)$st['id'];
            $aScores = $assignScores[$uid] ?? [];
            $qScores = $quizScores[$uid]   ?? [];
            $att  = $attMap[$uid] ?? null;

            $aAvg = $this->_avg(array_values($aScores));
            $qAvg = $this->_avg(array_values($qScores));

            $final  = $this->_calcFinal($aAvg, $qAvg, $att, $weights);
            $letter = $this->_letter($final);
            $isOv   = isset($overrides[$uid]);
            if ($isOv) {
                $final  = (float)$overrides[$uid]['final_grade'];
                $letter = $overrides[$uid]['letter_grade'] ?? $this->_letter($final);
            }

            $students[] = [
                'id'               => $uid,
                'name'             => $st['name'],
                'email'            => $st['email'],
                'assignment_scores'=> $aScores,
                'quiz_scores'      => $qScores,
                'assign_avg'       => $aAvg,
                'quiz_avg'         => $qAvg,
                'att_pct'          => $att,
                'final'            => $final,
                'letter'           => $letter,
                'is_overridden'    => $isOv,
            ];
        }
        return $students;
    }

    private function _calcFinal(?float $a, ?float $q, ?float $att, array $w): ?float
    {
        $total  = 0.0; $weight = 0.0;
        if ($a   !== null) { $total += $a   * $w['assignments_pct']; $weight += $w['assignments_pct']; }
        if ($q   !== null) { $total += $q   * $w['quizzes_pct'];     $weight += $w['quizzes_pct'];     }
        if ($att !== null) { $total += $att * $w['attendance_pct'];  $weight += $w['attendance_pct'];  }
        return $weight > 0 ? round($total / $weight, 1) : null;
    }

    private function _letter(?float $pct): string
    {
        if ($pct === null) return '—';
        foreach (self::SCALE as $s) {
            if ($pct >= $s['min']) return $s['letter'];
        }
        return 'F';
    }

    private function _avg(array $vals): ?float
    {
        $vals = array_values(array_filter($vals, fn($v) => $v !== null));
        return count($vals) > 0 ? round(array_sum($vals) / count($vals), 1) : null;
    }

    private function _getWeights(int $courseId): array
    {
        try {
            $this->db->query("SELECT * FROM lms_grade_weights WHERE course_id=?", [$courseId]);
            $w = $this->db->fetch();
            if ($w) return $w;
        } catch (\Throwable $e) {}
        return ['assignments_pct' => 40, 'quizzes_pct' => 40, 'attendance_pct' => 20];
    }

    private function _getCourseAssignments(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT id, title, max_score, due_at FROM lms_assignments WHERE course_id=? AND deleted_at IS NULL ORDER BY due_at, id",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getCourseQuizzes(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT id, title, pass_percentage FROM lms_quizzes WHERE course_id=? AND deleted_at IS NULL ORDER BY created_at",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getCourseTitle(int $courseId): string
    {
        try {
            $this->db->query("SELECT title FROM lms_courses WHERE id=?", [$courseId]);
            return $this->db->fetch()['title'] ?? '';
        } catch (\Throwable $e) { return ''; }
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

    // ── Sync LMS grades → Academic assessment_marks ──────────
    public function syncToAcademic(int $courseId): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('gradebook.manage');

        // Load course with subject link
        $this->db->query(
            "SELECT c.*, c.subject_id FROM lms_courses c WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courseId]
        );
        $course = $this->db->fetch();

        if (!$course || empty($course['subject_id'])) {
            $this->json(['status' => 'error', 'message' => 'Course has no linked academic subject.']);
            return;
        }

        $subjectId = (int)$course['subject_id'];
        $instId    = (int)$course['institution_id'];

        // Find or create assessment_config for this subject
        $this->db->query(
            "SELECT id FROM assessment_configs WHERE subject_id = ? AND institution_id = ? AND assessment_type = 'internal' LIMIT 1",
            [$subjectId, $instId]
        );
        $config = $this->db->fetch();

        if (!$config) {
            $configId = (int)$this->db->insert('assessment_configs', [
                'institution_id' => $instId,
                'subject_id'     => $subjectId,
                'assessment_type' => 'internal',
                'assessment_name' => 'LMS Assessment — ' . $course['title'],
                'max_marks'       => 100,
                'weightage'       => 100,
                'status'          => 'active',
                'created_by'      => $this->lmsUserId,
            ]);
        } else {
            $configId = (int)$config['id'];
        }

        // Get enrolled students with their final grades
        $this->db->query(
            "SELECT e.lms_user_id, e.score, e.progress,
                    lu.student_id
             FROM lms_enrollments e
             JOIN lms_users lu ON lu.id = e.lms_user_id
             WHERE e.course_id = ? AND lu.student_id IS NOT NULL",
            [$courseId]
        );
        $enrollments = $this->db->fetchAll();

        $synced = 0;
        foreach ($enrollments as $en) {
            $studentId = (int)$en['student_id'];
            $score     = $en['score'] ?? $en['progress'] ?? 0;

            // Upsert assessment_marks
            $this->db->query(
                "SELECT id FROM assessment_marks WHERE student_id = ? AND assessment_config_id = ? LIMIT 1",
                [$studentId, $configId]
            );
            $existing = $this->db->fetch();

            $letter = 'F';
            foreach (self::SCALE as $band) {
                if ($score >= $band['min']) { $letter = $band['letter']; break; }
            }

            $markData = [
                'marks_obtained' => round($score, 2),
                'grade'          => $letter,
                'remarks'        => 'Synced from LMS on ' . date('Y-m-d H:i'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->update('assessment_marks', $markData, 'id = ?', [$existing['id']]);
            } else {
                $this->db->insert('assessment_marks', array_merge($markData, [
                    'institution_id'       => $instId,
                    'student_id'           => $studentId,
                    'subject_id'           => $subjectId,
                    'assessment_config_id' => $configId,
                    'max_marks'            => 100,
                    'created_at'           => date('Y-m-d H:i:s'),
                ]));
            }
            $synced++;
        }

        $this->json(['status' => 'ok', 'synced' => $synced, 'message' => "{$synced} student grade(s) synced to academic module."]);
    }
}
