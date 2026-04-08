-- ============================================================
-- Module 11: LMS Analytics (supplementary tracking tables)
-- Core data comes from existing tables; these add aggregation hints
-- ============================================================

-- Daily activity rollups (filled by background job or on-demand compute)
CREATE TABLE IF NOT EXISTS lms_analytics_daily (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id  INT UNSIGNED NOT NULL,
    course_id       INT UNSIGNED NULL,
    date            DATE         NOT NULL,
    active_learners SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    lessons_completed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    quiz_attempts   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    assignment_subs SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    forum_posts     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    xp_awarded      INT UNSIGNED NOT NULL DEFAULT 0,

    UNIQUE KEY uq_daily (institution_id, course_id, date),
    INDEX idx_daily_inst (institution_id),
    INDEX idx_daily_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Content engagement: per-lesson view counts
CREATE TABLE IF NOT EXISTS lms_lesson_views (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id       INT UNSIGNED NOT NULL,
    lms_user_id     INT UNSIGNED NOT NULL,
    course_id       INT UNSIGNED NOT NULL,
    institution_id  INT UNSIGNED NOT NULL,
    viewed_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_lv_lesson (lesson_id),
    INDEX idx_lv_user   (lms_user_id),
    INDEX idx_lv_course (course_id),
    CONSTRAINT fk_lv_lesson FOREIGN KEY (lesson_id)    REFERENCES lms_lessons(id) ON DELETE CASCADE,
    CONSTRAINT fk_lv_user   FOREIGN KEY (lms_user_id)  REFERENCES lms_users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
