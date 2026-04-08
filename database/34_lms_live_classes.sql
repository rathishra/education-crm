-- ============================================================
-- Module 8: LMS Live Classes
-- ============================================================

CREATE TABLE IF NOT EXISTS lms_live_classes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id           INT UNSIGNED  NOT NULL,
    institution_id      INT UNSIGNED  NOT NULL,
    created_by          INT UNSIGNED  NOT NULL,   -- lms_users.id (instructor)
    title               VARCHAR(255)  NOT NULL,
    description         TEXT          NULL,
    platform            ENUM('zoom','google_meet','teams','webex','custom') NOT NULL DEFAULT 'zoom',
    meeting_url         VARCHAR(1024) NOT NULL,
    meeting_id          VARCHAR(255)  NULL,
    meeting_password    VARCHAR(255)  NULL,
    scheduled_at        DATETIME      NOT NULL,
    duration_mins       SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    max_participants    SMALLINT UNSIGNED NULL,
    status              ENUM('scheduled','live','ended','cancelled') NOT NULL DEFAULT 'scheduled',
    recording_url       VARCHAR(1024) NULL,
    recording_password  VARCHAR(255)  NULL,
    is_recorded         TINYINT(1)    NOT NULL DEFAULT 0,
    is_published        TINYINT(1)    NOT NULL DEFAULT 1,
    host_notes          TEXT          NULL,
    started_at          DATETIME      NULL,
    ended_at            DATETIME      NULL,
    created_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_live_course  (course_id),
    INDEX idx_live_inst    (institution_id),
    INDEX idx_live_sched   (scheduled_at),
    INDEX idx_live_status  (status),
    CONSTRAINT fk_live_course FOREIGN KEY (course_id)      REFERENCES lms_courses(id)      ON DELETE CASCADE,
    CONSTRAINT fk_live_inst   FOREIGN KEY (institution_id) REFERENCES institutions(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_live_registrations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    live_class_id   INT UNSIGNED NOT NULL,
    lms_user_id     INT UNSIGNED NOT NULL,
    registered_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    joined_at       DATETIME     NULL,
    left_at         DATETIME     NULL,
    duration_s      INT UNSIGNED NULL,     -- seconds actually in session
    attended        TINYINT(1)   NOT NULL DEFAULT 0,

    UNIQUE KEY uq_live_reg (live_class_id, lms_user_id),
    INDEX idx_live_reg_class (live_class_id),
    INDEX idx_live_reg_user  (lms_user_id),
    CONSTRAINT fk_live_reg_class FOREIGN KEY (live_class_id) REFERENCES lms_live_classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_live_reg_user  FOREIGN KEY (lms_user_id)   REFERENCES lms_users(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
