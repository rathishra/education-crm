<?php
namespace App\Controllers\Lms;

class QuizController extends LmsBaseController
{
    // ── Index ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('quizzes.view');

        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 15;
        $offset   = ($page - 1) * $perPage;
        $search   = trim($this->input('search', ''));
        $courseId = (int)$this->input('course', 0);

        if ($this->isInstructor()) {
            [$quizzes, $total] = $this->_instructorList($search, $courseId, $perPage, $offset);
        } else {
            [$quizzes, $total] = $this->_learnerList($search, $courseId, $perPage, $offset);
        }

        $totalPages = (int)ceil($total / $perPage) ?: 1;
        $myCourses  = $this->_getMyCourses();
        $pageTitle  = 'Quizzes';
        $this->view('lms/quizzes/index', compact(
            'quizzes', 'total', 'page', 'totalPages',
            'search', 'courseId', 'myCourses', 'pageTitle'
        ), 'main');
    }

    // ── Create form ───────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('quizzes.create');
        $courses   = $this->_getMyCourses();
        $courseId  = (int)$this->input('course_id', 0);
        $lessons   = $courseId ? $this->_getLessons($courseId) : [];
        $pageTitle = 'Create Quiz';
        $this->view('lms/quizzes/form', compact('courses', 'courseId', 'lessons', 'pageTitle'), 'main');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('quizzes.create');
        $data   = $_POST;
        $errors = $this->_validateQuiz($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $id = $this->db->insert('lms_quizzes', [
                'course_id'         => (int)$data['course_id'],
                'lesson_id'         => !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null,
                'institution_id'    => $this->institutionId,
                'created_by'        => $this->lmsUserId,
                'title'             => trim($data['title']),
                'description'       => trim($data['description'] ?? ''),
                'time_limit_mins'   => !empty($data['time_limit_mins']) ? (int)$data['time_limit_mins'] : null,
                'attempts_allowed'  => max(0, (int)($data['attempts_allowed'] ?: 1)),
                'pass_percentage'   => min(100, max(1, (int)($data['pass_percentage'] ?: 60))),
                'shuffle_questions' => isset($data['shuffle_questions']) ? 1 : 0,
                'shuffle_options'   => isset($data['shuffle_options'])   ? 1 : 0,
                'show_result'       => $data['show_result'] ?? 'immediately',
                'show_correct'      => isset($data['show_correct']) ? 1 : 0,
                'is_published'      => isset($data['is_published']) ? 1 : 0,
                'due_at'            => !empty($data['due_at']) ? $data['due_at'] : null,
            ]);

            $this->_syncDeadlines((int)$id, (int)$data['course_id'], $data['due_at'] ?? null, trim($data['title']));
            $this->audit('quiz.created', 'quiz', (int)$id, ['title' => $data['title']]);
            flash('success', "Quiz created. Now add questions.");
            redirect(url("elms/quizzes/{$id}/builder"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create quiz.']);
            back();
        }
    }

    // ── Edit form ─────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('quizzes.create');
        $quiz    = $this->_findQuiz($id, true);
        $courses = $this->_getMyCourses();
        $lessons = $this->_getLessons((int)$quiz['course_id']);
        $pageTitle = 'Edit Quiz';
        $this->view('lms/quizzes/form', compact('quiz', 'courses', 'lessons', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->authorize('quizzes.create');
        $this->_findQuiz($id, true);
        $data   = $_POST;
        $errors = $this->_validateQuiz($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $this->db->query(
                "UPDATE lms_quizzes SET
                    title=?,description=?,time_limit_mins=?,attempts_allowed=?,pass_percentage=?,
                    shuffle_questions=?,shuffle_options=?,show_result=?,show_correct=?,
                    is_published=?,due_at=?,lesson_id=?
                 WHERE id=? AND institution_id=?",
                [
                    trim($data['title']),
                    trim($data['description'] ?? ''),
                    !empty($data['time_limit_mins']) ? (int)$data['time_limit_mins'] : null,
                    max(0, (int)($data['attempts_allowed'] ?: 1)),
                    min(100, max(1, (int)($data['pass_percentage'] ?: 60))),
                    isset($data['shuffle_questions']) ? 1 : 0,
                    isset($data['shuffle_options'])   ? 1 : 0,
                    $data['show_result'] ?? 'immediately',
                    isset($data['show_correct']) ? 1 : 0,
                    isset($data['is_published'])  ? 1 : 0,
                    !empty($data['due_at']) ? $data['due_at'] : null,
                    !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null,
                    $id, $this->institutionId,
                ]
            );
            $this->_syncDeadlines($id, (int)$data['course_id'], $data['due_at'] ?? null, trim($data['title']));
            flash('success', 'Quiz updated.');
            redirect(url("elms/quizzes/{$id}/builder"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update quiz.']);
            back();
        }
    }

    // ── Delete ────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->authorize('quizzes.create');
        $this->_findQuiz($id, true);
        try {
            $this->db->query("UPDATE lms_quizzes SET deleted_at=NOW() WHERE id=?", [$id]);
            flash('success', 'Quiz deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/quizzes'));
    }

    // ── Question Builder ──────────────────────────────────────
    public function builder(int $id): void
    {
        $this->authorize('quizzes.create');
        $quiz      = $this->_findQuiz($id, true);
        $questions = $this->_loadQuestions($id);
        $pageTitle = 'Quiz Builder — ' . $quiz['title'];
        $this->view('lms/quizzes/builder', compact('quiz', 'questions', 'pageTitle'), 'main');
    }

    // ── Save question (AJAX) ──────────────────────────────────
    public function saveQuestion(int $quizId): void
    {
        $this->authorize('quizzes.create');
        $this->_findQuiz($quizId, true);

        $qid      = (int)$this->input('question_id', 0);
        $type     = $this->input('type', 'mcq');
        $question = trim($this->input('question', ''));
        $explanation = trim($this->input('explanation', ''));
        $points   = max(1, (int)$this->input('points', 1));
        $order    = (int)$this->input('sort_order', 0);
        $options  = (array)$this->input('options', []);   // [{text, is_correct, match_pair}]

        if (!$question) { $this->json(['error' => 'Question text required'], 400); return; }

        try {
            if ($qid) {
                $this->db->query(
                    "UPDATE lms_quiz_questions SET type=?,question=?,explanation=?,points=?,sort_order=? WHERE id=? AND quiz_id=?",
                    [$type, $question, $explanation ?: null, $points, $order, $qid, $quizId]
                );
                $this->db->query("DELETE FROM lms_quiz_options WHERE question_id=?", [$qid]);
            } else {
                $this->db->query(
                    "SELECT COALESCE(MAX(sort_order),0)+1 AS nxt FROM lms_quiz_questions WHERE quiz_id=?",
                    [$quizId]
                );
                $order = (int)($this->db->fetch()['nxt'] ?? 1);
                $qid = (int)$this->db->insert('lms_quiz_questions', [
                    'quiz_id'     => $quizId,
                    'type'        => $type,
                    'question'    => $question,
                    'explanation' => $explanation ?: null,
                    'points'      => $points,
                    'sort_order'  => $order,
                ]);
            }

            // Save options for choice-based types
            if (in_array($type, ['mcq','multi','true_false','match'])) {
                foreach ($options as $i => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if (!$text) continue;
                    $this->db->insert('lms_quiz_options', [
                        'question_id' => $qid,
                        'option_text' => $text,
                        'is_correct'  => !empty($opt['is_correct']) ? 1 : 0,
                        'match_pair'  => trim($opt['match_pair'] ?? '') ?: null,
                        'sort_order'  => $i,
                    ]);
                }
            }

            $this->json(['status' => 'ok', 'question_id' => $qid]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to save question'], 500);
        }
    }

    // ── Delete question (AJAX) ────────────────────────────────
    public function deleteQuestion(int $quizId, int $qid): void
    {
        $this->authorize('quizzes.create');
        try {
            $this->db->query("DELETE FROM lms_quiz_questions WHERE id=? AND quiz_id=?", [$qid, $quizId]);
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Reorder questions (AJAX) ──────────────────────────────
    public function reorderQuestions(int $quizId): void
    {
        $this->authorize('quizzes.create');
        foreach ((array)$this->input('order', []) as $idx => $qid) {
            try {
                $this->db->query(
                    "UPDATE lms_quiz_questions SET sort_order=? WHERE id=? AND quiz_id=?",
                    [(int)$idx, (int)$qid, $quizId]
                );
            } catch (\Throwable $e) {}
        }
        $this->json(['status' => 'ok']);
    }

    // ── Show quiz (learner takes the quiz) ────────────────────
    public function show(int $id): void
    {
        $this->authorize('quizzes.view');
        $quiz = $this->_findQuiz($id);

        if ($this->isInstructor()) {
            $this->_showInstructor($quiz);
            return;
        }

        // Learner — check enrollment
        try {
            $this->db->query(
                "SELECT * FROM lms_enrollments WHERE course_id=? AND lms_user_id=?",
                [$quiz['course_id'], $this->lmsUserId]
            );
            $enrollment = $this->db->fetch();
        } catch (\Throwable $e) { $enrollment = null; }

        // Previous attempts
        try {
            $this->db->query(
                "SELECT * FROM lms_quiz_attempts WHERE quiz_id=? AND lms_user_id=? ORDER BY attempt DESC",
                [$id, $this->lmsUserId]
            );
            $attempts = $this->db->fetchAll();
        } catch (\Throwable $e) { $attempts = []; }

        $attemptCount    = count($attempts);
        $attemptsAllowed = (int)$quiz['attempts_allowed'];
        $canStart        = $enrollment && ($attemptsAllowed === 0 || $attemptCount < $attemptsAllowed);
        $lastAttempt     = $attempts[0] ?? null;

        $pageTitle = $quiz['title'];
        $this->view('lms/quizzes/intro', compact(
            'quiz', 'attempts', 'attemptCount', 'canStart', 'lastAttempt', 'pageTitle'
        ), 'main');
    }

    private function _showInstructor(array $quiz): void
    {
        $id = (int)$quiz['id'];
        try {
            $this->db->query(
                "SELECT qa.*,
                        CONCAT(u.first_name,' ',u.last_name) AS learner_name,
                        u.email AS learner_email
                 FROM lms_quiz_attempts qa
                 JOIN lms_users u ON u.id = qa.lms_user_id
                 WHERE qa.quiz_id=? AND qa.status != 'in_progress'
                 ORDER BY qa.submitted_at DESC",
                [$id]
            );
            $attempts = $this->db->fetchAll();

            $stats = [
                'attempts'  => count($attempts),
                'passed'    => count(array_filter($attempts, fn($a) => $a['passed'])),
                'avg_score' => count($attempts) ? round(array_sum(array_column($attempts, 'percentage')) / count($attempts), 1) : 0,
                'avg_time'  => count($attempts) ? round(array_sum(array_column($attempts, 'time_taken_s')) / count($attempts)) : 0,
            ];
        } catch (\Throwable $e) {
            $attempts = [];
            $stats    = ['attempts' => 0, 'passed' => 0, 'avg_score' => 0, 'avg_time' => 0];
        }

        $questions  = $this->_loadQuestions($id);
        $totalPoints = array_sum(array_column($questions, 'points'));
        $pageTitle  = $quiz['title'];
        $this->view('lms/quizzes/results_instructor', compact(
            'quiz', 'attempts', 'stats', 'questions', 'totalPoints', 'pageTitle'
        ), 'main');
    }

    // ── Start attempt (POST) ──────────────────────────────────
    public function startAttempt(int $id): void
    {
        $this->authorize('quizzes.view');
        $quiz = $this->_findQuiz($id);
        if ($this->isInstructor()) { redirect(url("elms/quizzes/{$id}")); return; }

        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_quiz_attempts WHERE quiz_id=? AND lms_user_id=?",
                [$id, $this->lmsUserId]
            );
            $cnt = (int)($this->db->fetch()['cnt'] ?? 0);
            $allowed = (int)$quiz['attempts_allowed'];
            if ($allowed > 0 && $cnt >= $allowed) {
                flash('errors', ['Maximum attempts reached.']); redirect(url("elms/quizzes/{$id}")); return;
            }

            $attemptId = (int)$this->db->insert('lms_quiz_attempts', [
                'quiz_id'     => $id,
                'lms_user_id' => $this->lmsUserId,
                'attempt'     => $cnt + 1,
                'status'      => 'in_progress',
            ]);

            redirect(url("elms/quizzes/{$id}/attempt/{$attemptId}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to start quiz.']); redirect(url("elms/quizzes/{$id}"));
        }
    }

    // ── Take quiz ─────────────────────────────────────────────
    public function take(int $id, int $attemptId): void
    {
        $this->authorize('quizzes.view');
        $quiz    = $this->_findQuiz($id);
        $attempt = $this->_findAttempt($attemptId, $id);

        if ($attempt['status'] !== 'in_progress') {
            redirect(url("elms/quizzes/{$id}/attempt/{$attemptId}/result"));
            return;
        }

        $questions = $this->_loadQuestions($id);
        if ($quiz['shuffle_questions']) shuffle($questions);
        foreach ($questions as &$q) {
            if ($quiz['shuffle_options'] && !empty($q['options'])) shuffle($q['options']);
        }
        unset($q);

        $pageTitle = $quiz['title'];
        $this->view('lms/quizzes/take', compact('quiz', 'attempt', 'questions', 'pageTitle'), 'main');
    }

    // ── Submit attempt ────────────────────────────────────────
    public function submitAttempt(int $id, int $attemptId): void
    {
        $this->authorize('quizzes.view');
        $quiz    = $this->_findQuiz($id);
        $attempt = $this->_findAttempt($attemptId, $id);

        if ($attempt['status'] !== 'in_progress') {
            $this->json(['error' => 'Attempt already submitted'], 400); return;
        }

        $answers   = (array)$this->input('answers', []); // {question_id: [option_ids] or "text"}
        $questions = $this->_loadQuestions($id);
        $totalPts  = 0;
        $earnedPts = 0;

        foreach ($questions as $q) {
            $qid       = (int)$q['id'];
            $submitted = $answers[$qid] ?? null;
            $qPoints   = (float)$q['points'];
            $totalPts += $qPoints;

            $isCorrect   = null;
            $ptEarned    = 0;
            $optionIds   = null;
            $textAnswer  = null;

            switch ($q['type']) {
                case 'mcq':
                case 'true_false':
                    $selected = is_array($submitted) ? (int)($submitted[0] ?? 0) : (int)$submitted;
                    $optionIds = json_encode([$selected]);
                    $correctOpt = array_filter($q['options'], fn($o) => (int)$o['is_correct']);
                    $correctId  = $correctOpt ? (int)array_values($correctOpt)[0]['id'] : 0;
                    $isCorrect  = ($selected === $correctId && $selected > 0);
                    $ptEarned   = $isCorrect ? $qPoints : 0;
                    break;

                case 'multi':
                    $selected   = array_map('intval', (array)$submitted);
                    $optionIds  = json_encode($selected);
                    $correctIds = array_map(fn($o) => (int)$o['id'], array_values(array_filter($q['options'], fn($o) => (int)$o['is_correct'])));
                    sort($selected); sort($correctIds);
                    $isCorrect  = ($selected === $correctIds);
                    $ptEarned   = $isCorrect ? $qPoints : 0;
                    break;

                case 'short':
                case 'fill_blank':
                    $textAnswer = trim((string)$submitted);
                    $isCorrect  = null; // manual grading
                    $ptEarned   = 0;
                    break;
            }

            $earnedPts += $ptEarned;

            try {
                $this->db->query(
                    "INSERT INTO lms_quiz_answers
                        (attempt_id, question_id, option_ids, text_answer, is_correct, points_earned)
                     VALUES (?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                        option_ids=VALUES(option_ids), text_answer=VALUES(text_answer),
                        is_correct=VALUES(is_correct), points_earned=VALUES(points_earned)",
                    [$attemptId, $qid, $optionIds, $textAnswer, $isCorrect, $ptEarned]
                );
            } catch (\Throwable $e) {}
        }

        $pct    = $totalPts > 0 ? round($earnedPts / $totalPts * 100, 2) : 0;
        $passed = $pct >= (float)$quiz['pass_percentage'];
        $taken  = max(1, time() - strtotime($attempt['started_at']));

        try {
            $this->db->query(
                "UPDATE lms_quiz_attempts SET
                    status='submitted', score=?, max_score=?, percentage=?, passed=?,
                    time_taken_s=?, submitted_at=NOW()
                 WHERE id=?",
                [$earnedPts, $totalPts, $pct, $passed ? 1 : 0, $taken, $attemptId]
            );

            // XP & activity
            if ($passed) {
                $this->db->query("UPDATE lms_users SET xp_points=xp_points+20 WHERE id=?", [$this->lmsUserId]);
            }
            $this->db->query(
                "INSERT INTO lms_activity_feed
                    (lms_user_id, institution_id, event, entity_type, entity_id, entity_title, xp_earned)
                 VALUES (?,?,'quiz_submitted','quiz',?,?,?)",
                [$this->lmsUserId, $this->institutionId, $id, $quiz['title'], $passed ? 20 : 0]
            );

            // Update deadline
            $this->db->query(
                "UPDATE lms_deadlines SET is_submitted=1 WHERE entity_id=? AND lms_user_id=? AND type='quiz'",
                [$id, $this->lmsUserId]
            );
        } catch (\Throwable $e) {}

        if ($this->isAjax()) {
            $this->json(['status' => 'ok', 'attempt_id' => $attemptId, 'passed' => $passed, 'percentage' => $pct]);
            return;
        }
        redirect(url("elms/quizzes/{$id}/attempt/{$attemptId}/result"));
    }

    // ── Result page ───────────────────────────────────────────
    public function result(int $id, int $attemptId): void
    {
        $this->authorize('quizzes.view');
        $quiz    = $this->_findQuiz($id);
        $attempt = $this->_findAttempt($attemptId, $id);

        if ($attempt['status'] === 'in_progress') { redirect(url("elms/quizzes/{$id}/attempt/{$attemptId}")); return; }

        $showResult  = $quiz['show_result'] === 'immediately' ||
                       ($quiz['show_result'] === 'after_due' && (!$quiz['due_at'] || strtotime($quiz['due_at']) < time()));
        $showCorrect = (bool)$quiz['show_correct'];

        $answers = [];
        if ($showResult) {
            try {
                $this->db->query(
                    "SELECT a.*, q.question, q.type, q.explanation, q.points
                     FROM lms_quiz_answers a
                     JOIN lms_quiz_questions q ON q.id = a.question_id
                     WHERE a.attempt_id=?
                     ORDER BY q.sort_order",
                    [$attemptId]
                );
                $rawAnswers = $this->db->fetchAll();
                foreach ($rawAnswers as &$ans) {
                    $ans['option_ids'] = json_decode($ans['option_ids'] ?? '[]', true) ?: [];
                    if ($showCorrect) {
                        $this->db->query(
                            "SELECT * FROM lms_quiz_options WHERE question_id=? ORDER BY sort_order",
                            [$ans['question_id']]
                        );
                        $ans['options'] = $this->db->fetchAll();
                    }
                }
                unset($ans);
                $answers = $rawAnswers;
            } catch (\Throwable $e) {}
        }

        $pageTitle = 'Quiz Result — ' . $quiz['title'];
        $this->view('lms/quizzes/result', compact(
            'quiz', 'attempt', 'answers', 'showResult', 'showCorrect', 'pageTitle'
        ), 'main');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findQuiz(int $id, bool $instructorOnly = false): array
    {
        try {
            $this->db->query(
                "SELECT q.*, c.title AS course_title FROM lms_quizzes q
                 JOIN lms_courses c ON c.id = q.course_id
                 WHERE q.id=? AND q.institution_id=? AND q.deleted_at IS NULL",
                [$id, $this->institutionId]
            );
            $quiz = $this->db->fetch();
        } catch (\Throwable $e) { $quiz = null; }
        if (!$quiz) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Quiz Not Found'], 'main');
            exit;
        }
        if ($instructorOnly && !$this->isInstructor()) {
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied'], 'main');
            exit;
        }
        return $quiz;
    }

    private function _findAttempt(int $attemptId, int $quizId): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_quiz_attempts WHERE id=? AND quiz_id=? AND lms_user_id=?",
                [$attemptId, $quizId, $this->lmsUserId]
            );
            $a = $this->db->fetch();
        } catch (\Throwable $e) { $a = null; }
        if (!$a) {
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied'], 'main');
            exit;
        }
        return $a;
    }

    private function _loadQuestions(int $quizId): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_quiz_questions WHERE quiz_id=? ORDER BY sort_order, id",
                [$quizId]
            );
            $questions = $this->db->fetchAll();
            foreach ($questions as &$q) {
                $this->db->query(
                    "SELECT * FROM lms_quiz_options WHERE question_id=? ORDER BY sort_order",
                    [$q['id']]
                );
                $q['options'] = $this->db->fetchAll();
            }
            unset($q);
            return $questions;
        } catch (\Throwable $e) { return []; }
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

    private function _getLessons(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT l.id, l.title, s.title AS section_title
                 FROM lms_lessons l JOIN lms_course_sections s ON s.id=l.section_id
                 WHERE l.course_id=? AND l.deleted_at IS NULL ORDER BY s.sort_order, l.sort_order",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _syncDeadlines(int $quizId, int $courseId, ?string $dueAt, string $title): void
    {
        if (!$dueAt) return;
        try {
            $this->db->query("DELETE FROM lms_deadlines WHERE entity_id=? AND type='quiz'", [$quizId]);
            $this->db->query("SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active'", [$courseId]);
            foreach ($this->db->fetchAll() as $e) {
                $this->db->insert('lms_deadlines', [
                    'institution_id' => $this->institutionId,
                    'course_id'      => $courseId,
                    'lms_user_id'    => $e['lms_user_id'],
                    'type'           => 'quiz',
                    'entity_id'      => $quizId,
                    'title'          => $title,
                    'due_at'         => $dueAt,
                ]);
            }
        } catch (\Throwable $e) {}
    }

    private function _instructorList(string $search, int $courseId, int $perPage, int $offset): array
    {
        $where  = ['q.institution_id=?','q.deleted_at IS NULL'];
        $params = [$this->institutionId];
        if (!$this->isAdmin()) { $where[] = 'q.created_by=?'; $params[] = $this->lmsUserId; }
        if ($search)  { $where[] = 'q.title LIKE ?'; $params[] = "%{$search}%"; }
        if ($courseId){ $where[] = 'q.course_id=?';  $params[] = $courseId; }
        $w = implode(' AND ', $where);
        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_quizzes q WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT q.*, c.title AS course_title,
                        (SELECT COUNT(*) FROM lms_quiz_questions WHERE quiz_id=q.id) AS q_count,
                        (SELECT COUNT(*) FROM lms_quiz_attempts WHERE quiz_id=q.id AND status!='in_progress') AS attempt_count
                 FROM lms_quizzes q JOIN lms_courses c ON c.id=q.course_id
                 WHERE {$w} ORDER BY q.created_at DESC LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _learnerList(string $search, int $courseId, int $perPage, int $offset): array
    {
        $where  = ['q.institution_id=?','q.deleted_at IS NULL','q.is_published=1','e.lms_user_id=?'];
        $params = [$this->institutionId, $this->lmsUserId];
        if ($search)  { $where[] = 'q.title LIKE ?'; $params[] = "%{$search}%"; }
        if ($courseId){ $where[] = 'q.course_id=?';  $params[] = $courseId; }
        $w = implode(' AND ', $where);
        try {
            $this->db->query("SELECT COUNT(DISTINCT q.id) AS cnt FROM lms_quizzes q JOIN lms_enrollments e ON e.course_id=q.course_id WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT q.*, c.title AS course_title,
                        (SELECT COUNT(*) FROM lms_quiz_questions WHERE quiz_id=q.id) AS q_count,
                        (SELECT COUNT(*) FROM lms_quiz_attempts WHERE quiz_id=q.id AND lms_user_id=? AND status!='in_progress') AS my_attempts,
                        (SELECT MAX(percentage) FROM lms_quiz_attempts WHERE quiz_id=q.id AND lms_user_id=? AND status!='in_progress') AS best_pct,
                        (SELECT MAX(passed) FROM lms_quiz_attempts WHERE quiz_id=q.id AND lms_user_id=?) AS ever_passed
                 FROM lms_quizzes q JOIN lms_courses c ON c.id=q.course_id
                 JOIN lms_enrollments e ON e.course_id=q.course_id
                 WHERE {$w}
                 ORDER BY ISNULL(q.due_at), q.due_at ASC LIMIT ? OFFSET ?",
                array_merge([$this->lmsUserId, $this->lmsUserId, $this->lmsUserId], $params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _validateQuiz(array $d): array
    {
        $errors = [];
        if (empty(trim($d['title'] ?? '')))  $errors['title']     = 'Title required.';
        if (empty($d['course_id'] ?? ''))    $errors['course_id'] = 'Please select a course.';
        return $errors;
    }
}
