<?php
namespace App\Services;

/**
 * LmsAutoSync — Lightweight static helpers to keep Academic and LMS data in sync.
 * Called from Academic/Admin controllers whenever CRUD operations happen.
 */
class LmsAutoSync
{
    /**
     * Ensure a student has a matching lms_users record (role=learner).
     * Call after student creation or update.
     */
    public static function syncStudent(int $studentId, int $institutionId): ?int
    {
        $db = db();
        try {
            $db->query("SELECT id FROM lms_users WHERE student_id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
                [$studentId, $institutionId]);
            $existing = $db->fetch();
            if ($existing) return (int)$existing['id'];

            $db->query("SELECT first_name, last_name, email FROM students WHERE id = ? LIMIT 1", [$studentId]);
            $s = $db->fetch();
            if (!$s) return null;

            return (int)$db->insert('lms_users', [
                'institution_id' => $institutionId,
                'student_id'     => $studentId,
                'first_name'     => $s['first_name'],
                'last_name'      => $s['last_name'] ?? '',
                'email'          => $s['email'] ?? '',
                'role'           => 'learner',
                'status'         => 'active',
                'xp_points'      => 0,
                'level'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ensure a faculty member has a matching lms_users record (role=instructor).
     * Call after faculty creation or update.
     */
    public static function syncFaculty(int $userId, int $institutionId): ?int
    {
        $db = db();
        try {
            $db->query("SELECT id FROM lms_users WHERE staff_user_id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
                [$userId, $institutionId]);
            $existing = $db->fetch();
            if ($existing) return (int)$existing['id'];

            $db->query("SELECT first_name, last_name, email FROM users WHERE id = ? LIMIT 1", [$userId]);
            $u = $db->fetch();
            if (!$u) return null;

            return (int)$db->insert('lms_users', [
                'institution_id' => $institutionId,
                'staff_user_id'  => $userId,
                'first_name'     => $u['first_name'],
                'last_name'      => $u['last_name'] ?? '',
                'email'          => $u['email'] ?? '',
                'role'           => 'instructor',
                'status'         => 'active',
                'xp_points'      => 0,
                'level'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ensure an academic subject has a matching lms_courses record.
     * Creates a draft course linked via subject_id if none exists.
     */
    public static function syncSubject(int $subjectId, int $institutionId): ?int
    {
        $db = db();
        try {
            $db->query("SELECT id FROM lms_courses WHERE subject_id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
                [$subjectId, $institutionId]);
            $existing = $db->fetch();
            if ($existing) return (int)$existing['id'];

            $db->query("SELECT * FROM subjects WHERE id = ? LIMIT 1", [$subjectId]);
            $sub = $db->fetch();
            if (!$sub) return null;

            // Try to find an instructor from faculty_subject_allocations
            $db->query(
                "SELECT lu.id FROM faculty_subject_allocations fsa
                 JOIN users u ON u.id = fsa.faculty_id
                 JOIN lms_users lu ON lu.staff_user_id = u.id AND lu.institution_id = ?
                 WHERE fsa.subject_id = ? LIMIT 1",
                [$institutionId, $subjectId]
            );
            $inst = $db->fetch();
            $instructorId = $inst ? (int)$inst['id'] : null;

            // Fallback: first admin LMS user
            if (!$instructorId) {
                $db->query("SELECT id FROM lms_users WHERE institution_id = ? AND role IN ('lms_admin','instructor') AND status = 'active' LIMIT 1",
                    [$institutionId]);
                $fallback = $db->fetch();
                $instructorId = $fallback ? (int)$fallback['id'] : 1;
            }

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $sub['subject_name']), '-'));

            return (int)$db->insert('lms_courses', [
                'institution_id'    => $institutionId,
                'instructor_id'     => $instructorId,
                'subject_id'        => $subjectId,
                'title'             => $sub['subject_name'],
                'slug'              => $slug . '-' . $subjectId,
                'code'              => $sub['subject_code'] ?? null,
                'short_description' => ($sub['subject_code'] ?? '') . ' — ' . $sub['subject_name'],
                'level'             => 'all_levels',
                'status'            => 'draft',
                'visibility'        => 'enrolled',
                'language'          => 'English',
                'pass_percentage'   => 40,
                'total_lessons'     => 0,
                'enrolled_count'    => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Enroll an LMS learner into an LMS course (idempotent).
     */
    public static function syncEnrollment(int $lmsUserId, int $lmsCourseId, int $institutionId): ?int
    {
        $db = db();
        try {
            $db->query("SELECT id FROM lms_enrollments WHERE lms_user_id = ? AND course_id = ? LIMIT 1",
                [$lmsUserId, $lmsCourseId]);
            if ($db->fetch()) return null; // already enrolled

            return (int)$db->insert('lms_enrollments', [
                'institution_id' => $institutionId,
                'lms_user_id'    => $lmsUserId,
                'course_id'      => $lmsCourseId,
                'status'         => 'active',
                'progress'       => 0,
                'enrolled_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Bulk-enroll a student into all LMS courses for their batch subjects.
     */
    public static function enrollStudentInBatchCourses(int $lmsUserId, int $batchId, int $institutionId): int
    {
        $db = db();
        $count = 0;
        try {
            $db->query(
                "SELECT DISTINCT lc.id AS lms_course_id
                 FROM lms_courses lc
                 JOIN subjects s ON s.id = lc.subject_id
                 JOIN faculty_subject_allocations fsa ON fsa.subject_id = s.id
                 WHERE fsa.batch_id = ? AND lc.institution_id = ?
                   AND lc.deleted_at IS NULL AND lc.status IN ('published','active','draft')",
                [$batchId, $institutionId]
            );
            foreach ($db->fetchAll() as $row) {
                $result = self::syncEnrollment($lmsUserId, (int)$row['lms_course_id'], $institutionId);
                if ($result) $count++;
            }
        } catch (\Throwable $e) {}
        return $count;
    }
}
