-- ============================================================
-- Module 7: LMS Attendance
-- ============================================================

CREATE TABLE IF NOT EXISTS lms_attendance_sessions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id       INT UNSIGNED NOT NULL,
    institution_id  INT UNSIGNED NOT NULL,
    created_by      INT UNSIGNED NOT NULL,          -- lms_users.id
    title           VARCHAR(255)  NOT NULL,
    session_date    DATE          NOT NULL,
    start_time      TIME          NULL,
    end_time        TIME          NULL,
    type            ENUM('online','offline','live') NOT NULL DEFAULT 'offline',
    notes           TEXT          NULL,
    is_locked       TINYINT(1)    NOT NULL DEFAULT 0,  -- locked after marking done
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_att_sess_course    (course_id),
    INDEX idx_att_sess_inst      (institution_id),
    INDEX idx_att_sess_date      (session_date),
    CONSTRAINT fk_att_sess_course  FOREIGN KEY (course_id)  REFERENCES lms_courses(id)   ON DELETE CASCADE,
    CONSTRAINT fk_att_sess_inst    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lms_attendance_records (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      INT UNSIGNED NOT NULL,
    lms_user_id     INT UNSIGNED NOT NULL,
    status          ENUM('present','absent','late','excused') NOT NULL DEFAULT 'absent',
    marked_by       INT UNSIGNED NULL,              -- lms_users.id of instructor
    notes           VARCHAR(255) NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_att_rec (session_id, lms_user_id),
    INDEX idx_att_rec_user    (lms_user_id),
    INDEX idx_att_rec_session (session_id),
    CONSTRAINT fk_att_rec_session FOREIGN KEY (session_id)   REFERENCES lms_attendance_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_rec_user    FOREIGN KEY (lms_user_id)  REFERENCES lms_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Convenience view: per-enrollment attendance summary
CREATE OR REPLACE VIEW lms_attendance_summary AS
SELECT
    e.course_id,
    e.lms_user_id,
    COUNT(DISTINCT s.id)                                          AS total_sessions,
    COUNT(DISTINCT CASE WHEN r.status='present'  THEN r.id END)  AS present_count,
    COUNT(DISTINCT CASE WHEN r.status='late'     THEN r.id END)  AS late_count,
    COUNT(DISTINCT CASE WHEN r.status='excused'  THEN r.id END)  AS excused_count,
    COUNT(DISTINCT CASE WHEN r.status='absent'   THEN r.id END)  AS absent_count,
    ROUND(
        100.0 * COUNT(DISTINCT CASE WHEN r.status IN('present','late','excused') THEN r.id END)
        / NULLIF(COUNT(DISTINCT s.id), 0)
    , 1) AS attendance_pct
FROM lms_enrollments e
JOIN lms_attendance_sessions s ON s.course_id = e.course_id
LEFT JOIN lms_attendance_records r
    ON r.session_id = s.id AND r.lms_user_id = e.lms_user_id
GROUP BY e.course_id, e.lms_user_id;
