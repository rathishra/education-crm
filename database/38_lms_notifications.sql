-- ============================================================
-- Module 12: LMS Notifications
-- ============================================================

CREATE TABLE IF NOT EXISTS lms_notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id  INT UNSIGNED  NOT NULL,
    lms_user_id     INT UNSIGNED  NOT NULL,         -- recipient
    sender_id       INT UNSIGNED  NULL,             -- NULL = system
    type            VARCHAR(60)   NOT NULL,          -- assignment_graded | quiz_result | new_reply | live_reminder | etc.
    title           VARCHAR(255)  NOT NULL,
    body            TEXT          NULL,
    link            VARCHAR(512)  NULL,             -- relative URL to navigate to
    icon            VARCHAR(50)   NOT NULL DEFAULT 'fas fa-bell',
    color           VARCHAR(7)    NOT NULL DEFAULT '#6366f1',
    is_read         TINYINT(1)    NOT NULL DEFAULT 0,
    read_at         DATETIME      NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notif_user    (lms_user_id, is_read, created_at),
    INDEX idx_notif_inst    (institution_id),
    INDEX idx_notif_type    (type),
    CONSTRAINT fk_notif_user FOREIGN KEY (lms_user_id)   REFERENCES lms_users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Announcement broadcasts (course-wide or institution-wide)
CREATE TABLE IF NOT EXISTS lms_announcements (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id  INT UNSIGNED  NOT NULL,
    course_id       INT UNSIGNED  NULL,              -- NULL = institution-wide
    created_by      INT UNSIGNED  NOT NULL,          -- lms_users.id
    title           VARCHAR(255)  NOT NULL,
    body            TEXT          NOT NULL,
    type            ENUM('info','warning','success','danger') NOT NULL DEFAULT 'info',
    is_published    TINYINT(1)    NOT NULL DEFAULT 1,
    publish_at      DATETIME      NULL,              -- scheduled publish
    expires_at      DATETIME      NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ann_course (course_id),
    INDEX idx_ann_inst   (institution_id),
    CONSTRAINT fk_ann_course FOREIGN KEY (course_id)      REFERENCES lms_courses(id)  ON DELETE SET NULL,
    CONSTRAINT fk_ann_inst   FOREIGN KEY (institution_id) REFERENCES institutions(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Track which users have dismissed an announcement
CREATE TABLE IF NOT EXISTS lms_announcement_dismissals (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT UNSIGNED NOT NULL,
    lms_user_id     INT UNSIGNED NOT NULL,
    dismissed_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_dismissal (announcement_id, lms_user_id),
    CONSTRAINT fk_dis_ann  FOREIGN KEY (announcement_id) REFERENCES lms_announcements(id) ON DELETE CASCADE,
    CONSTRAINT fk_dis_user FOREIGN KEY (lms_user_id)     REFERENCES lms_users(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
