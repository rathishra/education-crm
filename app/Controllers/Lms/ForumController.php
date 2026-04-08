<?php
namespace App\Controllers\Lms;

class ForumController extends LmsBaseController
{
    // ── Index — thread list ───────────────────────────────────
    public function index(): void
    {
        $this->authorize('forum.view');

        $courseId   = (int)$this->input('course', 0);
        $categoryId = (int)$this->input('category', 0);
        $search     = trim($this->input('search', ''));
        $filter     = $this->input('filter', 'latest');  // latest | popular | solved | unsolved | mine
        $page       = max(1, (int)$this->input('page', 1));
        $perPage    = 20;
        $offset     = ($page - 1) * $perPage;

        [$threads, $total] = $this->_listThreads($courseId, $categoryId, $search, $filter, $perPage, $offset);
        $totalPages  = (int)ceil($total / $perPage) ?: 1;
        $myCourses   = $this->_getMyCourses();
        $categories  = $this->_getCategories($courseId);
        $pageTitle   = 'Discussion Forum';

        $this->view('lms/forum/index', compact(
            'threads', 'total', 'page', 'totalPages',
            'courseId', 'categoryId', 'search', 'filter',
            'myCourses', 'categories', 'pageTitle'
        ), 'main');
    }

    // ── Show thread ───────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('forum.view');
        $thread = $this->_findThread($id);

        // Increment view count (once per session)
        $sessKey = "forum_viewed_{$id}";
        if (empty($_SESSION[$sessKey])) {
            try { $this->db->query("UPDATE lms_forum_threads SET views=views+1 WHERE id=?", [$id]); } catch (\Throwable $e) {}
            $_SESSION[$sessKey] = 1;
        }

        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_forum_posts WHERE thread_id=? AND deleted_at IS NULL",
                [$id]
            );
            $postTotal = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT p.*,
                        CONCAT(u.first_name,' ',u.last_name) AS author_name,
                        u.email AS author_email,
                        u.role  AS author_role,
                        (SELECT COUNT(*) FROM lms_forum_reactions r WHERE r.post_id=p.id) AS likes,
                        EXISTS(SELECT 1 FROM lms_forum_reactions r WHERE r.post_id=p.id AND r.lms_user_id=?) AS i_liked
                 FROM lms_forum_posts p
                 JOIN lms_users u ON u.id=p.author_id
                 WHERE p.thread_id=? AND p.deleted_at IS NULL
                 ORDER BY p.is_solution DESC, p.created_at ASC
                 LIMIT ? OFFSET ?",
                [$this->lmsUserId, $id, $perPage, $offset]
            );
            $posts = $this->db->fetchAll();
        } catch (\Throwable $e) { $posts = []; $postTotal = 0; }

        $postTotalPages = (int)ceil($postTotal / $perPage) ?: 1;

        // Subscription check
        $isSubscribed = false;
        try {
            $this->db->query(
                "SELECT 1 FROM lms_forum_subscriptions WHERE thread_id=? AND lms_user_id=?",
                [$id, $this->lmsUserId]
            );
            $isSubscribed = (bool)$this->db->fetch();
        } catch (\Throwable $e) {}

        $pageTitle = $thread['title'];
        $this->view('lms/forum/thread', compact(
            'thread', 'posts', 'postTotal', 'postTotalPages', 'page',
            'isSubscribed', 'pageTitle'
        ), 'main');
    }

    // ── Create thread form ────────────────────────────────────
    public function create(): void
    {
        $this->authorize('forum.post');
        $courseId   = (int)$this->input('course_id', 0);
        $courses    = $this->_getMyCourses();
        $categories = $this->_getCategories($courseId);
        $pageTitle  = 'New Discussion';
        $this->view('lms/forum/create', compact('courses', 'courseId', 'categories', 'pageTitle'), 'main');
    }

    // ── Store thread ──────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('forum.post');
        $title  = trim($this->input('title', ''));
        $body   = trim($this->input('body', ''));
        $courseId   = (int)$this->input('course_id', 0);
        $categoryId = (int)$this->input('category_id', 0);

        if (!$title || !$body) { flash('errors', ['Title and body are required.']); back(); return; }

        try {
            $threadId = (int)$this->db->insert('lms_forum_threads', [
                'course_id'      => $courseId ?: null,
                'category_id'    => $categoryId ?: null,
                'institution_id' => $this->institutionId,
                'author_id'      => $this->lmsUserId,
                'title'          => $title,
                'body'           => $body,
                'last_post_by'   => $this->lmsUserId,
                'last_post_at'   => date('Y-m-d H:i:s'),
            ]);

            // Auto-subscribe OP
            $this->db->query(
                "INSERT IGNORE INTO lms_forum_subscriptions (thread_id, lms_user_id) VALUES (?,?)",
                [$threadId, $this->lmsUserId]
            );

            // XP: +3 for starting a thread
            $this->db->query("UPDATE lms_users SET xp_points=xp_points+3 WHERE id=?", [$this->lmsUserId]);
            $this->db->query(
                "INSERT INTO lms_activity_feed
                    (lms_user_id, institution_id, event, entity_type, entity_id, entity_title, xp_earned)
                 VALUES (?,?,'thread_created','forum_thread',?,?,3)",
                [$this->lmsUserId, $this->institutionId, $threadId, $title]
            );

            $this->audit('forum.thread_created', 'forum_thread', $threadId, ['title' => $title]);
            flash('success', 'Discussion started!');
            redirect(url("elms/forum/{$threadId}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create thread.']);
            back();
        }
    }

    // ── Reply (AJAX) ──────────────────────────────────────────
    public function reply(int $threadId): void
    {
        $this->authorize('forum.post');
        $thread = $this->_findThread($threadId);

        if ($thread['is_locked'] && !$this->isInstructor()) {
            $this->json(['error' => 'Thread is locked'], 403); return;
        }

        $body = trim($this->input('body', ''));
        if (strlen($body) < 2) { $this->json(['error' => 'Reply cannot be empty'], 400); return; }

        try {
            $postId = (int)$this->db->insert('lms_forum_posts', [
                'thread_id' => $threadId,
                'author_id' => $this->lmsUserId,
                'body'      => $body,
            ]);

            $this->db->query(
                "UPDATE lms_forum_threads SET
                    reply_count=reply_count+1, last_post_at=NOW(), last_post_by=?
                 WHERE id=?",
                [$this->lmsUserId, $threadId]
            );

            // XP: +2 for replying
            $this->db->query("UPDATE lms_users SET xp_points=xp_points+2 WHERE id=?", [$this->lmsUserId]);

            // Auto-subscribe replier
            $this->db->query(
                "INSERT IGNORE INTO lms_forum_subscriptions (thread_id, lms_user_id) VALUES (?,?)",
                [$threadId, $this->lmsUserId]
            );

            // Fetch the post back with author info for response
            $this->db->query(
                "SELECT p.*,
                        CONCAT(u.first_name,' ',u.last_name) AS author_name,
                        u.role AS author_role,
                        0 AS likes, 0 AS i_liked
                 FROM lms_forum_posts p JOIN lms_users u ON u.id=p.author_id
                 WHERE p.id=?",
                [$postId]
            );
            $post = $this->db->fetch();
            $this->json(['status' => 'ok', 'post' => $post]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to post reply'], 500);
        }
    }

    // ── Edit post (AJAX) ─────────────────────────────────────
    public function editPost(int $threadId, int $postId): void
    {
        $this->authorize('forum.post');
        $post = $this->_findPost($postId, $threadId);

        if ((int)$post['author_id'] !== $this->lmsUserId && !$this->isInstructor()) {
            $this->json(['error' => 'Forbidden'], 403); return;
        }

        $body = trim($this->input('body', ''));
        if (strlen($body) < 2) { $this->json(['error' => 'Body required'], 400); return; }

        try {
            $this->db->query("UPDATE lms_forum_posts SET body=? WHERE id=?", [$body, $postId]);
            $this->json(['status' => 'ok', 'body' => $body]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Delete post (soft) ────────────────────────────────────
    public function deletePost(int $threadId, int $postId): void
    {
        $this->authorize('forum.post');
        $post = $this->_findPost($postId, $threadId);

        if ((int)$post['author_id'] !== $this->lmsUserId && !$this->isInstructor()) {
            $this->json(['error' => 'Forbidden'], 403); return;
        }

        try {
            $this->db->query("UPDATE lms_forum_posts SET deleted_at=NOW() WHERE id=?", [$postId]);
            $this->db->query(
                "UPDATE lms_forum_threads SET reply_count=GREATEST(0,reply_count-1) WHERE id=?",
                [$threadId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Delete thread ─────────────────────────────────────────
    public function deleteThread(int $id): void
    {
        $this->authorize('forum.post');
        $thread = $this->_findThread($id);

        if ((int)$thread['author_id'] !== $this->lmsUserId && !$this->isInstructor()) {
            flash('errors', ['Forbidden.']); back(); return;
        }

        try {
            $this->db->query("UPDATE lms_forum_threads SET deleted_at=NOW() WHERE id=?", [$id]);
            flash('success', 'Thread deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/forum'));
    }

    // ── Mark solution (AJAX) ──────────────────────────────────
    public function markSolution(int $threadId, int $postId): void
    {
        $this->authorize('forum.post');
        $thread = $this->_findThread($threadId);

        // Only OP or instructor can mark solution
        if ((int)$thread['author_id'] !== $this->lmsUserId && !$this->isInstructor()) {
            $this->json(['error' => 'Forbidden'], 403); return;
        }

        try {
            // Unmark any previous solution
            $this->db->query("UPDATE lms_forum_posts SET is_solution=0 WHERE thread_id=?", [$threadId]);

            $isSolution = (int)$thread['solution_post_id'] === $postId ? 0 : 1;
            if ($isSolution) {
                $this->db->query("UPDATE lms_forum_posts SET is_solution=1 WHERE id=?", [$postId]);
            }

            $this->db->query(
                "UPDATE lms_forum_threads SET is_solved=?, solution_post_id=? WHERE id=?",
                [$isSolution, $isSolution ? $postId : null, $threadId]
            );

            // XP: +5 for answer marked as solution
            if ($isSolution) {
                $this->db->query(
                    "SELECT author_id FROM lms_forum_posts WHERE id=?", [$postId]
                );
                $aRow = $this->db->fetch();
                if ($aRow) {
                    $this->db->query("UPDATE lms_users SET xp_points=xp_points+5 WHERE id=?", [$aRow['author_id']]);
                }
            }

            $this->json(['status' => 'ok', 'is_solution' => $isSolution]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── React / like (AJAX toggle) ────────────────────────────
    public function react(int $threadId, int $postId): void
    {
        $this->authorize('forum.view');
        $this->_findPost($postId, $threadId);

        try {
            $this->db->query(
                "SELECT 1 FROM lms_forum_reactions WHERE post_id=? AND lms_user_id=?",
                [$postId, $this->lmsUserId]
            );
            $exists = (bool)$this->db->fetch();

            if ($exists) {
                $this->db->query(
                    "DELETE FROM lms_forum_reactions WHERE post_id=? AND lms_user_id=?",
                    [$postId, $this->lmsUserId]
                );
                $this->db->query("UPDATE lms_forum_posts SET like_count=GREATEST(0,like_count-1) WHERE id=?", [$postId]);
                $liked = false;
            } else {
                $this->db->query(
                    "INSERT IGNORE INTO lms_forum_reactions (post_id, lms_user_id) VALUES (?,?)",
                    [$postId, $this->lmsUserId]
                );
                $this->db->query("UPDATE lms_forum_posts SET like_count=like_count+1 WHERE id=?", [$postId]);
                $liked = true;
            }

            $this->db->query("SELECT like_count FROM lms_forum_posts WHERE id=?", [$postId]);
            $count = (int)($this->db->fetch()['like_count'] ?? 0);
            $this->json(['status' => 'ok', 'liked' => $liked, 'count' => $count]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Pin / Unpin (AJAX, instructor only) ───────────────────
    public function pin(int $id): void
    {
        $this->authorize('forum.moderate');
        $thread = $this->_findThread($id);
        $newVal = $thread['is_pinned'] ? 0 : 1;
        try {
            $this->db->query("UPDATE lms_forum_threads SET is_pinned=? WHERE id=?", [$newVal, $id]);
            $this->json(['status' => 'ok', 'is_pinned' => $newVal]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Lock / Unlock (AJAX, instructor only) ─────────────────
    public function lock(int $id): void
    {
        $this->authorize('forum.moderate');
        $thread = $this->_findThread($id);
        $newVal = $thread['is_locked'] ? 0 : 1;
        try {
            $this->db->query("UPDATE lms_forum_threads SET is_locked=? WHERE id=?", [$newVal, $id]);
            $this->json(['status' => 'ok', 'is_locked' => $newVal]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Subscribe / Unsubscribe (AJAX toggle) ────────────────
    public function subscribe(int $id): void
    {
        $this->authorize('forum.view');
        $this->_findThread($id);
        try {
            $this->db->query(
                "SELECT 1 FROM lms_forum_subscriptions WHERE thread_id=? AND lms_user_id=?",
                [$id, $this->lmsUserId]
            );
            if ($this->db->fetch()) {
                $this->db->query(
                    "DELETE FROM lms_forum_subscriptions WHERE thread_id=? AND lms_user_id=?",
                    [$id, $this->lmsUserId]
                );
                $this->json(['status' => 'ok', 'subscribed' => false]);
            } else {
                $this->db->query(
                    "INSERT IGNORE INTO lms_forum_subscriptions (thread_id, lms_user_id) VALUES (?,?)",
                    [$id, $this->lmsUserId]
                );
                $this->json(['status' => 'ok', 'subscribed' => true]);
            }
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Categories AJAX (for create form dynamic load) ────────
    public function categoriesAjax(): void
    {
        $this->authorize('forum.view');
        $courseId   = (int)$this->input('course_id', 0);
        $categories = $this->_getCategories($courseId);
        $this->json(['categories' => $categories]);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findThread(int $id): array
    {
        try {
            $this->db->query(
                "SELECT t.*,
                        CONCAT(u.first_name,' ',u.last_name) AS author_name,
                        u.role AS author_role,
                        cat.name AS category_name, cat.color AS category_color,
                        c.title AS course_title
                 FROM lms_forum_threads t
                 JOIN lms_users u ON u.id=t.author_id
                 LEFT JOIN lms_forum_categories cat ON cat.id=t.category_id
                 LEFT JOIN lms_courses c ON c.id=t.course_id
                 WHERE t.id=? AND t.institution_id=? AND t.deleted_at IS NULL",
                [$id, $this->institutionId]
            );
            $thread = $this->db->fetch();
        } catch (\Throwable $e) { $thread = null; }

        if (!$thread) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Thread Not Found'], 'main');
            exit;
        }
        return $thread;
    }

    private function _findPost(int $postId, int $threadId): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_forum_posts WHERE id=? AND thread_id=? AND deleted_at IS NULL",
                [$postId, $threadId]
            );
            $p = $this->db->fetch();
        } catch (\Throwable $e) { $p = null; }

        if (!$p) {
            $this->json(['error' => 'Post not found'], 404);
            exit;
        }
        return $p;
    }

    private function _listThreads(int $courseId, int $categoryId, string $search, string $filter, int $perPage, int $offset): array
    {
        $where  = ['t.institution_id=?', 't.deleted_at IS NULL'];
        $params = [$this->institutionId];

        if ($courseId)    { $where[] = 't.course_id=?';    $params[] = $courseId; }
        if ($categoryId)  { $where[] = 't.category_id=?';  $params[] = $categoryId; }
        if ($search)      { $where[] = 't.title LIKE ?';   $params[] = "%{$search}%"; }

        // Learners only see threads from courses they're enrolled in
        if ($this->isLearner() && !$courseId) {
            $where[] = "(t.course_id IS NULL OR t.course_id IN (SELECT course_id FROM lms_enrollments WHERE lms_user_id=? AND status='active'))";
            $params[] = $this->lmsUserId;
        }

        switch ($filter) {
            case 'solved':   $where[] = 't.is_solved=1'; break;
            case 'unsolved': $where[] = 't.is_solved=0'; break;
            case 'mine':     $where[] = 't.author_id=?'; $params[] = $this->lmsUserId; break;
        }

        $w   = implode(' AND ', $where);
        $ord = $filter === 'popular'
            ? 't.reply_count DESC, t.views DESC'
            : 't.is_pinned DESC, t.last_post_at DESC';

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_forum_threads t WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT t.*,
                        CONCAT(u.first_name,' ',u.last_name) AS author_name,
                        u.role AS author_role,
                        cat.name AS category_name, cat.color AS category_color,
                        c.title AS course_title,
                        CONCAT(lu.first_name,' ',lu.last_name) AS last_poster_name
                 FROM lms_forum_threads t
                 JOIN lms_users u ON u.id=t.author_id
                 LEFT JOIN lms_forum_categories cat ON cat.id=t.category_id
                 LEFT JOIN lms_courses c ON c.id=t.course_id
                 LEFT JOIN lms_users lu ON lu.id=t.last_post_by
                 WHERE {$w} ORDER BY {$ord} LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _getCategories(int $courseId): array
    {
        try {
            $where  = ['institution_id=?'];
            $params = [$this->institutionId];
            if ($courseId) { $where[] = '(course_id IS NULL OR course_id=?)'; $params[] = $courseId; }
            $this->db->query(
                "SELECT * FROM lms_forum_categories WHERE " . implode(' AND ', $where) . " ORDER BY sort_order, name",
                $params
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getMyCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id={$this->institutionId}"
                : ($this->isLearner()
                    ? "id IN (SELECT course_id FROM lms_enrollments WHERE lms_user_id={$this->lmsUserId} AND status='active')"
                    : "instructor_id={$this->lmsUserId} AND institution_id={$this->institutionId}");
            $this->db->query("SELECT id,title FROM lms_courses WHERE {$scope} AND deleted_at IS NULL ORDER BY title");
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }
}
