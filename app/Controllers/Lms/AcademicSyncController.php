<?php
namespace App\Controllers\Lms;

/**
 * AcademicSyncController
 *
 * Bridges the Academic module and the LMS module:
 *   • Students  → lms_users (role=learner,  linked via student_id)
 *   • Faculty   → lms_users (role=instructor, linked via staff_user_id)
 *   • Subjects  → lms_courses (linked via subject_id)
 *   • student_section_enrollments + faculty_subject_allocations → lms_enrollments
 */
class AcademicSyncController extends LmsBaseController
{
    // ── Dashboard ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('sync.manage');

        $stats = [
            'students' => [
                'academic' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM students
                     WHERE institution_id=? AND status='active' AND deleted_at IS NULL",
                    [$this->institutionId]
                ),
                'lms' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM lms_users
                     WHERE institution_id=? AND role='learner' AND student_id IS NOT NULL AND deleted_at IS NULL",
                    [$this->institutionId]
                ),
            ],
            'faculty' => [
                'academic' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM faculty_profiles
                     WHERE institution_id=? AND status='active'",
                    [$this->institutionId]
                ),
                'lms' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM lms_users
                     WHERE institution_id=? AND role='instructor' AND staff_user_id IS NOT NULL AND deleted_at IS NULL",
                    [$this->institutionId]
                ),
            ],
            'courses' => [
                'academic' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM subjects
                     WHERE institution_id=? AND status='active' AND deleted_at IS NULL",
                    [$this->institutionId]
                ),
                'lms' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM lms_courses
                     WHERE institution_id=? AND subject_id IS NOT NULL AND deleted_at IS NULL",
                    [$this->institutionId]
                ),
            ],
            'enrollments' => [
                'academic' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM student_section_enrollments
                     WHERE institution_id=? AND status='active'",
                    [$this->institutionId]
                ),
                'lms' => $this->_cnt(
                    "SELECT COUNT(*) AS c FROM lms_enrollments
                     WHERE institution_id=? AND status='active'",
                    [$this->institutionId]
                ),
            ],
        ];

        try {
            $this->db->query(
                "SELECT sl.*, lu.first_name, lu.last_name
                 FROM lms_academic_sync_log sl
                 LEFT JOIN lms_users lu ON lu.id = sl.synced_by
                 WHERE sl.institution_id=?
                 ORDER BY sl.created_at DESC LIMIT 30",
                [$this->institutionId]
            );
            $logs = $this->db->fetchAll();
        } catch (\Throwable $e) {
            $logs = [];
        }

        $pageTitle = 'Academic Sync';
        $this->view('lms/sync/index', compact('stats', 'logs', 'pageTitle'), 'main');
    }

    // ── Sync Students → LMS Learners ──────────────────────────────
    public function syncStudents(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('sync.manage');
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];

        try {
            $this->db->query(
                "SELECT id, institution_id, first_name, last_name, email,
                        phone, photo, status, student_id_number
                 FROM students
                 WHERE institution_id=? AND status='active' AND deleted_at IS NULL",
                [$this->institutionId]
            );
            $students = $this->db->fetchAll();

            foreach ($students as $s) {
                // Students without email get a generated placeholder
                $email = !empty($s['email'])
                    ? $s['email']
                    : strtolower(preg_replace('/\s+/','.', trim($s['first_name'].'.'.$s['last_name'])))
                      .'.'.$s['student_id_number'].'@lms.local';

                try {
                    $this->db->query(
                        "SELECT id, status FROM lms_users
                         WHERE student_id=? AND institution_id=?",
                        [$s['id'], $this->institutionId]
                    );
                    $existing = $this->db->fetch();

                    if ($existing) {
                        $this->db->query(
                            "UPDATE lms_users
                             SET first_name=?, last_name=?, display_name=?,
                                 phone=?, avatar=?, status='active', updated_at=NOW()
                             WHERE id=?",
                            [
                                $s['first_name'],
                                $s['last_name'] ?? '',
                                trim($s['first_name'].' '.($s['last_name'] ?? '')),
                                $s['phone'],
                                $s['photo'],
                                $existing['id'],
                            ]
                        );
                        $updated++;
                    } else {
                        $this->db->insert('lms_users', [
                            'institution_id'    => $this->institutionId,
                            'student_id'        => $s['id'],
                            'email'             => $email,
                            'password'          => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                            'first_name'        => $s['first_name'],
                            'last_name'         => $s['last_name'] ?? '',
                            'display_name'      => trim($s['first_name'].' '.($s['last_name'] ?? '')),
                            'avatar'            => $s['photo'],
                            'phone'             => $s['phone'],
                            'role'              => 'learner',
                            'status'            => 'active',
                            'email_verified_at' => date('Y-m-d H:i:s'),
                        ]);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                }
            }
        } catch (\Throwable $e) {
            $errors++;
        }

        $this->_log('students', $created, $updated, $skipped, $errors);
        $this->json(compact('created', 'updated', 'skipped', 'errors') + ['status' => 'ok']);
    }

    // ── Sync Faculty → LMS Instructors ────────────────────────────
    public function syncFaculty(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('sync.manage');
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];

        try {
            $this->db->query(
                "SELECT fp.id AS faculty_id, fp.user_id, fp.designation,
                        u.first_name, u.last_name, u.email, u.phone
                 FROM faculty_profiles fp
                 JOIN users u ON u.id = fp.user_id
                 WHERE fp.institution_id=? AND fp.status='active'",
                [$this->institutionId]
            );
            $faculty = $this->db->fetchAll();

            foreach ($faculty as $f) {
                try {
                    $this->db->query(
                        "SELECT id FROM lms_users
                         WHERE staff_user_id=? AND institution_id=?",
                        [$f['user_id'], $this->institutionId]
                    );
                    $existing = $this->db->fetch();

                    if ($existing) {
                        $this->db->query(
                            "UPDATE lms_users
                             SET first_name=?, last_name=?, display_name=?,
                                 role='instructor', status='active', updated_at=NOW()
                             WHERE id=?",
                            [
                                $f['first_name'], $f['last_name'] ?? '',
                                trim($f['first_name'].' '.($f['last_name'] ?? '')),
                                $existing['id'],
                            ]
                        );
                        $updated++;
                    } else {
                        $this->db->insert('lms_users', [
                            'institution_id'    => $this->institutionId,
                            'staff_user_id'     => $f['user_id'],
                            'email'             => $f['email'],
                            'password'          => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                            'first_name'        => $f['first_name'],
                            'last_name'         => $f['last_name'] ?? '',
                            'display_name'      => trim($f['first_name'].' '.($f['last_name'] ?? '')),
                            'role'              => 'instructor',
                            'status'            => 'active',
                            'email_verified_at' => date('Y-m-d H:i:s'),
                        ]);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                }
            }
        } catch (\Throwable $e) {
            $errors++;
        }

        $this->_log('faculty', $created, $updated, $skipped, $errors);
        $this->json(compact('created', 'updated', 'skipped', 'errors') + ['status' => 'ok']);
    }

    // ── Sync Subjects → LMS Courses ───────────────────────────────
    public function syncCourses(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('sync.manage');
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];

        try {
            // Load all active subjects with their primary faculty (via allocation)
            $this->db->query(
                "SELECT s.id AS subject_id, s.subject_code, s.subject_name,
                        s.subject_type, s.credits, s.semester, s.description,
                        s.course_id AS program_id,
                        c.name AS program_name,
                        d.name AS dept_name,
                        -- Pick first active faculty allocation
                        (SELECT lu.id FROM faculty_subject_allocations fsa
                         JOIN lms_users lu ON lu.staff_user_id = fsa.faculty_id
                                          AND lu.institution_id = s.institution_id
                                          AND lu.deleted_at IS NULL
                         WHERE fsa.subject_id = s.id AND fsa.status='active'
                         ORDER BY fsa.id LIMIT 1) AS lms_instructor_id
                 FROM subjects s
                 LEFT JOIN courses c ON c.id = s.course_id
                 LEFT JOIN departments d ON d.id = s.department_id
                 WHERE s.institution_id=? AND s.status='active' AND s.deleted_at IS NULL",
                [$this->institutionId]
            );
            $subjects = $this->db->fetchAll();

            foreach ($subjects as $sub) {
                try {
                    $this->db->query(
                        "SELECT id FROM lms_courses WHERE subject_id=? AND institution_id=?",
                        [$sub['subject_id'], $this->institutionId]
                    );
                    $existing = $this->db->fetch();

                    $instructorId = $sub['lms_instructor_id'] ?? $this->lmsUserId;
                    $shortDesc = $sub['description']
                        ?: ($sub['subject_name'].' — '.ucfirst($sub['subject_type'])
                            .($sub['semester'] ? ', Sem '.$sub['semester'] : '')
                            .($sub['credits'] ? ', '.$sub['credits'].' Credits' : ''));

                    if ($existing) {
                        $this->db->query(
                            "UPDATE lms_courses
                             SET title=?, code=?, short_description=?,
                                 instructor_id=?, updated_at=NOW()
                             WHERE id=?",
                            [
                                $sub['subject_name'],
                                $sub['subject_code'],
                                $shortDesc,
                                $instructorId,
                                $existing['id'],
                            ]
                        );
                        $updated++;
                    } else {
                        $slug = $this->_makeSlug(
                            $sub['subject_name'].'-'.$sub['subject_code']
                        );

                        $this->db->insert('lms_courses', [
                            'institution_id'    => $this->institutionId,
                            'instructor_id'     => $instructorId,
                            'subject_id'        => $sub['subject_id'],
                            'code'              => $sub['subject_code'],
                            'title'             => $sub['subject_name'],
                            'slug'              => $slug,
                            'short_description' => $shortDesc,
                            'description'       => $sub['description'],
                            'level'             => 'all_levels',
                            'status'            => 'published',
                            'visibility'        => 'enrolled',
                            'allow_self_enroll' => 0,
                            'pass_percentage'   => 40,
                        ]);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                }
            }
        } catch (\Throwable $e) {
            $errors++;
        }

        $this->_log('courses', $created, $updated, $skipped, $errors);
        $this->json(compact('created', 'updated', 'skipped', 'errors') + ['status' => 'ok']);
    }

    // ── Sync Enrollments ──────────────────────────────────────────
    // Logic:
    //   For each active student section enrollment:
    //     → find the student's lms_user
    //     → find all subjects taught in that batch (via faculty_subject_allocations)
    //     → find the matching lms_course (by subject_id)
    //     → create lms_enrollment if not already exists
    public function syncEnrollments(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('sync.manage');
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];

        try {
            $this->db->query(
                "SELECT DISTINCT
                        lu.id   AS lms_user_id,
                        lc.id   AS lms_course_id,
                        lc.institution_id
                 FROM student_section_enrollments sse
                 -- Student must have an LMS user record
                 JOIN lms_users lu
                      ON lu.student_id   = sse.student_id
                     AND lu.institution_id = sse.institution_id
                     AND lu.deleted_at IS NULL
                     AND lu.role = 'learner'
                 -- Batch must have at least one active faculty-subject allocation
                 JOIN faculty_subject_allocations fsa
                      ON fsa.batch_id  = sse.batch_id
                     AND fsa.status   = 'active'
                 -- That subject must have a synced LMS course
                 JOIN lms_courses lc
                      ON lc.subject_id      = fsa.subject_id
                     AND lc.institution_id  = sse.institution_id
                     AND lc.deleted_at IS NULL
                 WHERE sse.institution_id = ? AND sse.status = 'active'",
                [$this->institutionId]
            );
            $pairs = $this->db->fetchAll();

            foreach ($pairs as $p) {
                try {
                    $this->db->query(
                        "SELECT id, status FROM lms_enrollments
                         WHERE course_id=? AND lms_user_id=?",
                        [$p['lms_course_id'], $p['lms_user_id']]
                    );
                    $existing = $this->db->fetch();

                    if ($existing) {
                        // Re-activate if it was dropped
                        if ($existing['status'] !== 'active') {
                            $this->db->query(
                                "UPDATE lms_enrollments SET status='active', updated_at=NOW()
                                 WHERE id=?",
                                [$existing['id']]
                            );
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        $this->db->insert('lms_enrollments', [
                            'course_id'      => $p['lms_course_id'],
                            'lms_user_id'    => $p['lms_user_id'],
                            'institution_id' => $this->institutionId,
                            'enrolled_by'    => $this->lmsUserId,
                            'status'         => 'active',
                            'progress'       => 0,
                        ]);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                }
            }
        } catch (\Throwable $e) {
            $errors++;
        }

        $this->_log('enrollments', $created, $updated, $skipped, $errors);
        $this->json(compact('created', 'updated', 'skipped', 'errors') + ['status' => 'ok']);
    }

    // ── Sync All (runs all 4 steps in sequence) ───────────────────
    public function syncAll(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('sync.manage');
        $totals = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        foreach (['_runStudents', '_runFaculty', '_runCourses', '_runEnrollments'] as $fn) {
            $r = $this->$fn();
            foreach ($totals as $k => &$v) { $v += ($r[$k] ?? 0); }
        }

        $this->_log('all', $totals['created'], $totals['updated'], $totals['skipped'], $totals['errors']);
        $this->json(['status' => 'ok'] + $totals);
    }

    // ── Stats endpoint (AJAX, for polling after sync) ─────────────
    public function stats(): void
    {
        $this->authorize('sync.manage');
        $this->json([
            'students_academic' => $this->_cnt("SELECT COUNT(*) AS c FROM students WHERE institution_id=? AND status='active' AND deleted_at IS NULL", [$this->institutionId]),
            'students_lms'      => $this->_cnt("SELECT COUNT(*) AS c FROM lms_users WHERE institution_id=? AND role='learner' AND student_id IS NOT NULL AND deleted_at IS NULL", [$this->institutionId]),
            'faculty_academic'  => $this->_cnt("SELECT COUNT(*) AS c FROM faculty_profiles WHERE institution_id=? AND status='active'", [$this->institutionId]),
            'faculty_lms'       => $this->_cnt("SELECT COUNT(*) AS c FROM lms_users WHERE institution_id=? AND role='instructor' AND staff_user_id IS NOT NULL AND deleted_at IS NULL", [$this->institutionId]),
            'courses_academic'  => $this->_cnt("SELECT COUNT(*) AS c FROM subjects WHERE institution_id=? AND status='active' AND deleted_at IS NULL", [$this->institutionId]),
            'courses_lms'       => $this->_cnt("SELECT COUNT(*) AS c FROM lms_courses WHERE institution_id=? AND subject_id IS NOT NULL AND deleted_at IS NULL", [$this->institutionId]),
            'enroll_academic'   => $this->_cnt("SELECT COUNT(*) AS c FROM student_section_enrollments WHERE institution_id=? AND status='active'", [$this->institutionId]),
            'enroll_lms'        => $this->_cnt("SELECT COUNT(*) AS c FROM lms_enrollments WHERE institution_id=? AND status='active'", [$this->institutionId]),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // Internal runners (return result arrays, don't echo JSON)
    // ═══════════════════════════════════════════════════════════════

    private function _runStudents(): array
    {
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];
        try {
            $this->db->query(
                "SELECT id, institution_id, first_name, last_name, email, phone, photo, student_id_number
                 FROM students
                 WHERE institution_id=? AND status='active' AND deleted_at IS NULL",
                [$this->institutionId]
            );
            foreach ($this->db->fetchAll() as $s) {
                $email = !empty($s['email']) ? $s['email']
                    : strtolower(preg_replace('/\s+/', '.', trim($s['first_name'].'.'.$s['last_name'])))
                      .'.'.$s['student_id_number'].'@lms.local';
                try {
                    $this->db->query("SELECT id FROM lms_users WHERE student_id=? AND institution_id=?", [$s['id'], $this->institutionId]);
                    $ex = $this->db->fetch();
                    if ($ex) {
                        $this->db->query("UPDATE lms_users SET first_name=?,last_name=?,display_name=?,phone=?,avatar=?,status='active',updated_at=NOW() WHERE id=?",
                            [$s['first_name'], $s['last_name'] ?? '', trim($s['first_name'].' '.($s['last_name'] ?? '')), $s['phone'], $s['photo'], $ex['id']]);
                        $updated++;
                    } else {
                        $this->db->insert('lms_users', ['institution_id'=>$this->institutionId,'student_id'=>$s['id'],'email'=>$email,'password'=>password_hash(bin2hex(random_bytes(16)),PASSWORD_BCRYPT),'first_name'=>$s['first_name'],'last_name'=>$s['last_name']??'','display_name'=>trim($s['first_name'].' '.($s['last_name']??'')),'avatar'=>$s['photo'],'phone'=>$s['phone'],'role'=>'learner','status'=>'active','email_verified_at'=>date('Y-m-d H:i:s')]);
                        $created++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        } catch (\Throwable $e) { $errors++; }
        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function _runFaculty(): array
    {
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];
        try {
            $this->db->query(
                "SELECT fp.user_id, u.first_name, u.last_name, u.email, u.phone
                 FROM faculty_profiles fp JOIN users u ON u.id=fp.user_id
                 WHERE fp.institution_id=? AND fp.status='active'",
                [$this->institutionId]
            );
            foreach ($this->db->fetchAll() as $f) {
                try {
                    $this->db->query("SELECT id FROM lms_users WHERE staff_user_id=? AND institution_id=?", [$f['user_id'], $this->institutionId]);
                    $ex = $this->db->fetch();
                    if ($ex) {
                        $this->db->query("UPDATE lms_users SET first_name=?,last_name=?,display_name=?,role='instructor',status='active',updated_at=NOW() WHERE id=?",
                            [$f['first_name'],$f['last_name']??'',trim($f['first_name'].' '.($f['last_name']??'')),$ex['id']]);
                        $updated++;
                    } else {
                        $this->db->insert('lms_users', ['institution_id'=>$this->institutionId,'staff_user_id'=>$f['user_id'],'email'=>$f['email'],'password'=>password_hash(bin2hex(random_bytes(16)),PASSWORD_BCRYPT),'first_name'=>$f['first_name'],'last_name'=>$f['last_name']??'','display_name'=>trim($f['first_name'].' '.($f['last_name']??'')),'role'=>'instructor','status'=>'active','email_verified_at'=>date('Y-m-d H:i:s')]);
                        $created++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        } catch (\Throwable $e) { $errors++; }
        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function _runCourses(): array
    {
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];
        try {
            $this->db->query(
                "SELECT s.id AS subject_id, s.subject_code, s.subject_name, s.subject_type,
                        s.credits, s.semester, s.description,
                        (SELECT lu.id FROM faculty_subject_allocations fsa
                         JOIN lms_users lu ON lu.staff_user_id=fsa.faculty_id AND lu.institution_id=s.institution_id AND lu.deleted_at IS NULL
                         WHERE fsa.subject_id=s.id AND fsa.status='active' ORDER BY fsa.id LIMIT 1) AS lms_instructor_id
                 FROM subjects s
                 WHERE s.institution_id=? AND s.status='active' AND s.deleted_at IS NULL",
                [$this->institutionId]
            );
            foreach ($this->db->fetchAll() as $sub) {
                try {
                    $this->db->query("SELECT id FROM lms_courses WHERE subject_id=? AND institution_id=?", [$sub['subject_id'], $this->institutionId]);
                    $ex = $this->db->fetch();
                    $iid = $sub['lms_instructor_id'] ?? $this->lmsUserId;
                    $sd  = $sub['description'] ?: ($sub['subject_name'].' — '.ucfirst($sub['subject_type']).($sub['semester'] ? ', Sem '.$sub['semester'] : ''));
                    if ($ex) {
                        $this->db->query("UPDATE lms_courses SET title=?,code=?,short_description=?,instructor_id=?,updated_at=NOW() WHERE id=?",
                            [$sub['subject_name'], $sub['subject_code'], $sd, $iid, $ex['id']]);
                        $updated++;
                    } else {
                        $this->db->insert('lms_courses', ['institution_id'=>$this->institutionId,'instructor_id'=>$iid,'subject_id'=>$sub['subject_id'],'code'=>$sub['subject_code'],'title'=>$sub['subject_name'],'slug'=>$this->_makeSlug($sub['subject_name'].'-'.$sub['subject_code']),'short_description'=>$sd,'description'=>$sub['description'],'level'=>'all_levels','status'=>'published','visibility'=>'enrolled','allow_self_enroll'=>0,'pass_percentage'=>40]);
                        $created++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        } catch (\Throwable $e) { $errors++; }
        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function _runEnrollments(): array
    {
        [$created, $updated, $skipped, $errors] = [0, 0, 0, 0];
        try {
            $this->db->query(
                "SELECT DISTINCT lu.id AS lms_user_id, lc.id AS lms_course_id
                 FROM student_section_enrollments sse
                 JOIN lms_users lu ON lu.student_id=sse.student_id AND lu.institution_id=sse.institution_id AND lu.deleted_at IS NULL AND lu.role='learner'
                 JOIN faculty_subject_allocations fsa ON fsa.batch_id=sse.batch_id AND fsa.status='active'
                 JOIN lms_courses lc ON lc.subject_id=fsa.subject_id AND lc.institution_id=sse.institution_id AND lc.deleted_at IS NULL
                 WHERE sse.institution_id=? AND sse.status='active'",
                [$this->institutionId]
            );
            foreach ($this->db->fetchAll() as $p) {
                try {
                    $this->db->query("SELECT id,status FROM lms_enrollments WHERE course_id=? AND lms_user_id=?", [$p['lms_course_id'], $p['lms_user_id']]);
                    $ex = $this->db->fetch();
                    if ($ex) {
                        if ($ex['status'] !== 'active') { $this->db->query("UPDATE lms_enrollments SET status='active',updated_at=NOW() WHERE id=?",[$ex['id']]); $updated++; }
                        else { $skipped++; }
                    } else {
                        $this->db->insert('lms_enrollments', ['course_id'=>$p['lms_course_id'],'lms_user_id'=>$p['lms_user_id'],'institution_id'=>$this->institutionId,'enrolled_by'=>$this->lmsUserId,'status'=>'active','progress'=>0]);
                        $created++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        } catch (\Throwable $e) { $errors++; }
        return compact('created', 'updated', 'skipped', 'errors');
    }

    // ── Shared helpers ────────────────────────────────────────────

    private function _cnt(string $sql, array $p = []): int
    {
        try {
            $this->db->query($sql, $p);
            return (int)($this->db->fetch()['c'] ?? 0);
        } catch (\Throwable $e) { return 0; }
    }

    private function _makeSlug(string $str): string
    {
        $base = preg_replace('/[^a-z0-9\-]+/', '-', strtolower(trim($str)));
        $base = trim($base, '-');
        // Ensure uniqueness within institution
        $slug = $base;
        $i = 1;
        while (true) {
            $this->db->query(
                "SELECT id FROM lms_courses WHERE slug=? AND institution_id=?",
                [$slug, $this->institutionId]
            );
            if (!$this->db->fetch()) break;
            $slug = $base.'-'.($i++);
        }
        return $slug;
    }

    private function _log(string $type, int $c, int $u, int $s, int $e): void
    {
        try {
            $this->db->insert('lms_academic_sync_log', [
                'institution_id' => $this->institutionId,
                'sync_type'      => $type,
                'synced_by'      => $this->lmsUserId,
                'created_count'  => $c,
                'updated_count'  => $u,
                'skipped_count'  => $s,
                'error_count'    => $e,
            ]);
        } catch (\Throwable $e) {}
    }
}
