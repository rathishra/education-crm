-- ============================================================
-- Module 9: LMS Discussion Forum
-- ============================================================

CREATE TABLE IF NOT EXISTS lms_forum_categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id       INT UNSIGNED  NULL,            -- NULL = institution-wide
    institution_id  INT UNSIGNED  NOT NULL,
    name            VARCHAR(120)  NOT NULL,
    description     VARCHAR(255)  NULL,
    color           VARCHAR(7)    NOT NULL DEFAULT '#6366f1',
    icon            VARCHAR(50)   NOT NULL DEFAULT 'fas fa-comments',
    sort_order      SMALLINT      NOT NULL DEFAULT 0,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_fcat_course (course_id),
    INDEX idx_fcat_inst   (institution_id),
    CONSTRAINT fk_fcat_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_forum_threads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED  NULL,
    course_id       INT UNSIGNED  NULL,
    institution_id  INT UNSIGNED  NOT NULL,
    author_id       INT UNSIGNED  NOT NULL,        -- lms_users.id
    title           VARCHAR(255)  NOT NULL,
    body            TEXT          NOT NULL,
    is_pinned       TINYINT(1)    NOT NULL DEFAULT 0,
    is_locked       TINYINT(1)    NOT NULL DEFAULT 0,
    is_solved       TINYINT(1)    NOT NULL DEFAULT 0,
    solution_post_id INT UNSIGNED NULL,
    views           INT UNSIGNED  NOT NULL DEFAULT 0,
    reply_count     INT UNSIGNED  NOT NULL DEFAULT 0,
    last_post_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_post_by    INT UNSIGNED  NULL,
    deleted_at      DATETIME      NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_thread_course  (course_id),
    INDEX idx_thread_cat     (category_id),
    INDEX idx_thread_inst    (institution_id),
    INDEX idx_thread_author  (author_id),
    INDEX idx_thread_pinned  (is_pinned, last_post_at),
    CONSTRAINT fk_thread_inst   FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    CONSTRAINT fk_thread_author FOREIGN KEY (author_id)      REFERENCES lms_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_forum_posts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id       INT UNSIGNED  NOT NULL,
    author_id       INT UNSIGNED  NOT NULL,
    body            TEXT          NOT NULL,
    is_solution     TINYINT(1)    NOT NULL DEFAULT 0,
    like_count      INT UNSIGNED  NOT NULL DEFAULT 0,
    deleted_at      DATETIME      NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_post_thread (thread_id),
    INDEX idx_post_author (author_id),
    CONSTRAINT fk_post_thread FOREIGN KEY (thread_id) REFERENCES lms_forum_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_post_author FOREIGN KEY (author_id)  REFERENCES lms_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_forum_reactions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED  NOT NULL,
    lms_user_id INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_reaction (post_id, lms_user_id),
    CONSTRAINT fk_react_post FOREIGN KEY (post_id)     REFERENCES lms_forum_posts(id)  ON DELETE CASCADE,
    CONSTRAINT fk_react_user FOREIGN KEY (lms_user_id) REFERENCES lms_users(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_forum_subscriptions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id   INT UNSIGNED  NOT NULL,
    lms_user_id INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_sub (thread_id, lms_user_id),
    CONSTRAINT fk_sub_thread FOREIGN KEY (thread_id)   REFERENCES lms_forum_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_user   FOREIGN KEY (lms_user_id) REFERENCES lms_users(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
