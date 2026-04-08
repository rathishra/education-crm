-- ============================================================
-- Module 10: LMS Gradebook
-- ============================================================

-- Per-course grade component weights (must sum to 100)
CREATE TABLE IF NOT EXISTS lms_grade_weights (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id       INT UNSIGNED  NOT NULL,
    institution_id  INT UNSIGNED  NOT NULL,
    assignments_pct TINYINT UNSIGNED NOT NULL DEFAULT 40,
    quizzes_pct     TINYINT UNSIGNED NOT NULL DEFAULT 40,
    attendance_pct  TINYINT UNSIGNED NOT NULL DEFAULT 20,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_weights_course (course_id),
    CONSTRAINT fk_gw_course FOREIGN KEY (course_id) REFERENCES lms_courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_gw_inst   FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Manual grade overrides (instructor sets final grade directly)
CREATE TABLE IF NOT EXISTS lms_grade_overrides (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id       INT UNSIGNED  NOT NULL,
    lms_user_id     INT UNSIGNED  NOT NULL,
    institution_id  INT UNSIGNED  NOT NULL,
    final_grade     DECIMAL(5,2)  NOT NULL,           -- 0–100 percentage
    letter_grade    VARCHAR(5)    NULL,                -- A, B+, etc.
    override_note   TEXT          NULL,
    overridden_by   INT UNSIGNED  NOT NULL,            -- instructor lms_users.id
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_override (course_id, lms_user_id),
    CONSTRAINT fk_go_course FOREIGN KEY (course_id)    REFERENCES lms_courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_go_user   FOREIGN KEY (lms_user_id)  REFERENCES lms_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Cached/computed final grades (rebuilt on demand)
CREATE TABLE IF NOT EXISTS lms_computed_grades (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id           INT UNSIGNED  NOT NULL,
    lms_user_id         INT UNSIGNED  NOT NULL,
    institution_id      INT UNSIGNED  NOT NULL,
    assignment_avg      DECIMAL(5,2)  NULL,
    quiz_avg            DECIMAL(5,2)  NULL,
    attendance_pct      DECIMAL(5,2)  NULL,
    weighted_total      DECIMAL(5,2)  NULL,
    letter_grade        VARCHAR(5)    NULL,
    is_overridden       TINYINT(1)    NOT NULL DEFAULT 0,
    computed_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_computed (course_id, lms_user_id),
    CONSTRAINT fk_cg_course FOREIGN KEY (course_id)   REFERENCES lms_courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_cg_user   FOREIGN KEY (lms_user_id) REFERENCES lms_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
