<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;
use App\Services\TimetableEngine;

/**
 * TimetableGeneratorController
 *
 * Handles the full lifecycle of auto-generated timetables:
 *  dashboard → configure → generate → review → approve → published
 */
class TimetableGeneratorController extends BaseController
{
    // ── Dashboard ────────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->authorize('timetable.manage');

        $instId = $this->institutionId;

        // Recent runs
        $this->db->query(
            "SELECT r.*, c.name AS config_name,
                    u.first_name, u.last_name
             FROM timetable_generator_runs r
             LEFT JOIN timetable_generator_configs c ON c.id = r.config_id
             LEFT JOIN users u ON u.id = r.created_by
             WHERE r.institution_id = ?
             ORDER BY r.created_at DESC
             LIMIT 10",
            [$instId]
        );
        $runs = $this->db->fetchAll();

        // Active configs
        $this->db->query(
            "SELECT * FROM timetable_generator_configs
             WHERE institution_id = ? AND is_active = 1 ORDER BY name",
            [$instId]
        );
        $configs = $this->db->fetchAll();

        // Quick stats
        $this->db->query(
            "SELECT
                COUNT(DISTINCT s.id)                                          AS total_sections,
                SUM(CASE WHEN at.id IS NOT NULL THEN 1 ELSE 0 END)           AS sections_with_timetable,
                (SELECT COUNT(*) FROM timetable_generator_runs
                  WHERE institution_id = ? AND status = 'approved')          AS approved_runs,
                (SELECT COUNT(*) FROM timetable_subject_requirements tsr
                  INNER JOIN timetable_generator_configs tgc ON tgc.id = tsr.config_id
                  WHERE tgc.institution_id = ?)                              AS total_requirements
             FROM academic_sections s
             LEFT JOIN academic_timetable at ON at.section_id = s.id AND at.institution_id = ?
             WHERE s.institution_id = ?",
            [$instId, $instId, $instId, $instId]
        );
        $stats = $this->db->fetch() ?: [];

        $this->view('academic/timetable/generator_dashboard', compact('runs', 'configs', 'stats'));
    }

    // ── Configuration ────────────────────────────────────────────────────────

    public function configure(?int $configId = null): void
    {
        $this->authorize('timetable.manage');

        $instId = $this->institutionId;
        $config = null;
        $requirements = [];
        $constraints  = [];

        if ($configId) {
            $this->db->query(
                "SELECT * FROM timetable_generator_configs WHERE id = ? AND institution_id = ?",
                [$configId, $instId]
            );
            $config = $this->db->fetch();

            if ($config) {
                $this->db->query(
                    "SELECT r.*, s.subject_name, s.subject_code, s.subject_type,
                            sec.name AS section_name,
                            CONCAT(u.first_name,' ',u.last_name) AS faculty_name
                     FROM timetable_subject_requirements r
                     LEFT JOIN subjects s ON s.id = r.subject_id
                     LEFT JOIN academic_sections sec ON sec.id = r.section_id
                     LEFT JOIN users u ON u.id = r.faculty_id
                     WHERE r.config_id = ? AND r.institution_id = ?
                     ORDER BY sec.name, s.subject_name",
                    [$configId, $instId]
                );
                $requirements = $this->db->fetchAll();

                $this->db->query(
                    "SELECT * FROM timetable_generator_constraints
                     WHERE config_id = ? AND institution_id = ? AND is_active = 1
                     ORDER BY constraint_type DESC, constraint_key",
                    [$configId, $instId]
                );
                $constraints = $this->db->fetchAll();
            }
        }

        // Sections for requirement builder
        $this->db->query(
            "SELECT s.id, s.name, b.name AS batch_name, c.name AS course_name
             FROM academic_sections s
             LEFT JOIN academic_batches b ON b.id = s.batch_id
             LEFT JOIN courses c ON c.id = b.course_id
             WHERE s.institution_id = ?
             ORDER BY c.name, b.name, s.name",
            [$instId]
        );
        $sections = $this->db->fetchAll();

        // Subjects
        $this->db->query(
            "SELECT id, subject_name, subject_code, subject_type, hours_per_week
             FROM subjects WHERE institution_id = ? AND status = 'active'
             ORDER BY subject_name",
            [$instId]
        );
        $subjects = $this->db->fetchAll();

        // Faculty
        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$instId]
        );
        $faculty = $this->db->fetchAll();

        // Academic years
        $this->db->query(
            "SELECT id, year_name FROM academic_years
             WHERE institution_id = ? ORDER BY is_current DESC, year_name DESC LIMIT 5",
            [$instId]
        );
        $academicYears = $this->db->fetchAll();

        $this->view('academic/timetable/configure', compact(
            'config', 'requirements', 'constraints',
            'sections', 'subjects', 'faculty', 'academicYears', 'configId'
        ));
    }

    public function saveConfig(): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $instId = $this->institutionId;
        $data   = $this->postData();
        $configId = !empty($data['config_id']) ? (int)$data['config_id'] : null;

        $workingDays = 0;
        $dayBits = ['monday'=>1,'tuesday'=>2,'wednesday'=>4,'thursday'=>8,'friday'=>16,'saturday'=>32,'sunday'=>64];
        foreach (($data['working_days'] ?? []) as $day) {
            $workingDays |= ($dayBits[$day] ?? 0);
        }

        $row = [
            'institution_id'        => $instId,
            'academic_year_id'      => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'name'                  => sanitize($data['name'] ?? 'Default Config'),
            'working_days'          => $workingDays ?: 31,
            'max_periods_per_day'   => (int)($data['max_periods_per_day'] ?? 8),
            'max_consecutive_same'  => (int)($data['max_consecutive_same'] ?? 2),
            'avoid_first_last_same' => !empty($data['avoid_first_last_same']) ? 1 : 0,
            'distribute_evenly'     => !empty($data['distribute_evenly']) ? 1 : 0,
            'lab_block_size'        => (int)($data['lab_block_size'] ?? 2),
            'balance_faculty_load'  => !empty($data['balance_faculty_load']) ? 1 : 0,
            'created_by'            => $this->user['id'],
        ];

        if ($configId) {
            unset($row['institution_id'], $row['created_by']);
            $this->db->update('timetable_generator_configs', $row, 'id = ? AND institution_id = ?', [$configId, $instId]);
        } else {
            $configId = $this->db->insert('timetable_generator_configs', $row);
        }

        $this->logAudit('timetable_config_saved', 'timetable_generator_configs', $configId);
        $this->redirectWith(url("academic/timetable/generator/configure/{$configId}"), 'success', 'Configuration saved.');
    }

    public function saveRequirements(): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }

        $instId   = $this->institutionId;
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $configId = (int)($body['config_id'] ?? 0);
        $rows     = $body['requirements'] ?? [];

        if (!$configId) { jsonResponse(['success' => false, 'message' => 'Invalid config.'], 422); return; }

        // Verify config belongs to institution
        $this->db->query("SELECT id FROM timetable_generator_configs WHERE id = ? AND institution_id = ?", [$configId, $instId]);
        if (!$this->db->fetch()) { jsonResponse(['success' => false, 'message' => 'Config not found.'], 404); return; }

        // Upsert each requirement
        foreach ($rows as $r) {
            $sectionId = (int)($r['section_id'] ?? 0);
            $subjectId = (int)($r['subject_id'] ?? 0);
            if (!$sectionId || !$subjectId) continue;

            $existing = null;
            $this->db->query(
                "SELECT id FROM timetable_subject_requirements
                 WHERE config_id = ? AND section_id = ? AND subject_id = ? AND entry_type = ?",
                [$configId, $sectionId, $subjectId, $r['entry_type'] ?? 'lecture']
            );
            $existing = $this->db->fetch();

            $rowData = [
                'institution_id'   => $instId,
                'config_id'        => $configId,
                'section_id'       => $sectionId,
                'subject_id'       => $subjectId,
                'faculty_id'       => !empty($r['faculty_id']) ? (int)$r['faculty_id'] : null,
                'periods_per_week' => max(1, (int)($r['periods_per_week'] ?? 3)),
                'entry_type'       => in_array($r['entry_type'] ?? '', ['lecture','lab','tutorial','activity']) ? $r['entry_type'] : 'lecture',
                'preferred_room_id'=> !empty($r['preferred_room_id']) ? (int)$r['preferred_room_id'] : null,
                'priority'         => max(1, min(10, (int)($r['priority'] ?? 5))),
                'notes'            => sanitize($r['notes'] ?? ''),
            ];

            if ($existing) {
                unset($rowData['institution_id'], $rowData['config_id']);
                $this->db->update('timetable_subject_requirements', $rowData, 'id = ?', [$existing['id']]);
            } else {
                $this->db->insert('timetable_subject_requirements', $rowData);
            }
        }

        jsonResponse(['success' => true, 'message' => 'Requirements saved.']);
    }

    public function deleteRequirement(int $id): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }

        $this->db->query(
            "DELETE FROM timetable_subject_requirements
             WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        jsonResponse(['success' => true]);
    }

    // ── Auto-generation ───────────────────────────────────────────────────────

    public function generate(): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }

        $instId   = $this->institutionId;
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $configId = (int)($body['config_id'] ?? 0);
        $runName  = sanitize($body['run_name'] ?? ('Auto Run ' . date('d M Y H:i')));
        $sections = array_map('intval', $body['sections'] ?? []);

        // Validate config
        $this->db->query("SELECT * FROM timetable_generator_configs WHERE id = ? AND institution_id = ?", [$configId, $instId]);
        $config = $this->db->fetch();
        if (!$config) { jsonResponse(['success' => false, 'message' => 'Config not found.'], 404); return; }

        // Load requirements for the selected sections
        $sectionFilter = !empty($sections)
            ? "AND r.section_id IN (" . implode(',', $sections) . ")"
            : "";

        $this->db->query(
            "SELECT r.*, sec.batch_id
             FROM timetable_subject_requirements r
             INNER JOIN academic_sections sec ON sec.id = r.section_id
             WHERE r.config_id = ? AND r.institution_id = ? {$sectionFilter}",
            [$configId, $instId]
        );
        $requirements = $this->db->fetchAll();

        if (empty($requirements)) {
            jsonResponse(['success' => false, 'message' => 'No subject requirements configured. Please add requirements first.'], 422);
            return;
        }

        // Load periods
        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id = ? ORDER BY period_number",
            [$instId]
        );
        $periodsRaw = $this->db->fetchAll();
        $periods = [];
        foreach ($periodsRaw as $p) { $periods[$p['id']] = $p; }

        // Load sections
        $this->db->query(
            "SELECT id, name, batch_id FROM academic_sections WHERE institution_id = ?",
            [$instId]
        );
        $sectionsRaw = $this->db->fetchAll();
        $sectionsMap = [];
        foreach ($sectionsRaw as $s) { $sectionsMap[$s['id']] = $s; }

        // Load classrooms/rooms
        $this->db->query(
            "SELECT id, room_number, room_name, capacity, room_type
             FROM classrooms WHERE institution_id = ? AND is_active = 1",
            [$instId]
        );
        $roomsRaw = $this->db->fetchAll();
        $rooms = [];
        foreach ($roomsRaw as $r) { $rooms[$r['id']] = $r; }

        // Load faculty unavailability
        $this->db->query(
            "SELECT * FROM timetable_teacher_unavailability WHERE institution_id = ?",
            [$instId]
        );
        $unavail = $this->db->fetchAll();
        $facultyBlocked = [];
        foreach ($unavail as $u) {
            if ($u['period_id']) {
                $facultyBlocked[$u['faculty_id']][$u['day_of_week']][] = $u['period_id'];
            }
        }

        // Decode working days bitmask
        $bitmask = (int)$config['working_days'];
        $dayBits = ['monday'=>1,'tuesday'=>2,'wednesday'=>4,'thursday'=>8,'friday'=>16,'saturday'=>32,'sunday'=>64];
        $workingDays = [];
        foreach ($dayBits as $day => $bit) {
            if ($bitmask & $bit) $workingDays[] = $day;
        }

        // Create run record (status = running)
        $runId = $this->db->insert('timetable_generator_runs', [
            'institution_id'     => $instId,
            'config_id'          => $configId,
            'academic_year_id'   => $config['academic_year_id'],
            'run_name'           => $runName,
            'status'             => 'running',
            'total_requirements' => 0,
            'algorithm'          => 'greedy_backtrack',
            'sections_scope'     => json_encode($sections),
            'created_by'         => $this->user['id'],
        ]);

        try {
            $engine = new TimetableEngine();
            $engine->setData([
                'sections'        => $sectionsMap,
                'requirements'    => $requirements,
                'periods'         => $periods,
                'working_days'    => $workingDays,
                'rooms'           => $rooms,
                'faculty_blocked' => $facultyBlocked,
                'config'          => [
                    'max_consecutive_same'  => (int)$config['max_consecutive_same'],
                    'avoid_first_last_same' => (bool)$config['avoid_first_last_same'],
                    'distribute_evenly'     => (bool)$config['distribute_evenly'],
                    'lab_block_size'        => (int)$config['lab_block_size'],
                    'balance_faculty_load'  => (bool)$config['balance_faculty_load'],
                ],
            ]);

            $result = $engine->generate();

            // Update run record with results
            $this->db->update('timetable_generator_runs', [
                'status'             => 'completed',
                'total_requirements' => $result['total_requirements'],
                'assigned_count'     => $result['assigned_count'],
                'conflict_count'     => $result['conflict_count'],
                'score'              => $result['score'],
                'duration_ms'        => $result['duration_ms'],
                'result_payload'     => json_encode($result['assignments']),
                'conflict_payload'   => json_encode($result['conflicts']),
                'log'                => $result['log'],
            ], 'id = ?', [$runId]);

            $this->logAudit('timetable_generated', 'timetable_generator_runs', $runId);

            jsonResponse([
                'success'    => true,
                'run_id'     => $runId,
                'score'      => $result['score'],
                'assigned'   => $result['assigned_count'],
                'total'      => $result['total_requirements'],
                'conflicts'  => $result['conflict_count'],
                'message'    => "Generated {$result['assigned_count']} of {$result['total_requirements']} slots. Score: {$result['score']}%",
            ]);

        } catch (\Throwable $e) {
            $this->db->update('timetable_generator_runs', [
                'status' => 'failed',
                'log'    => $e->getMessage(),
            ], 'id = ?', [$runId]);

            appLog('TimetableGenerator failed: ' . $e->getMessage(), 'error');
            jsonResponse(['success' => false, 'message' => 'Generation failed: ' . $e->getMessage()], 500);
        }
    }

    // ── Run Review & Approval ─────────────────────────────────────────────────

    public function reviewRun(int $runId): void
    {
        $this->authorize('timetable.manage');

        $instId = $this->institutionId;

        $this->db->query(
            "SELECT r.*, c.name AS config_name, c.working_days,
                    u.first_name, u.last_name
             FROM timetable_generator_runs r
             LEFT JOIN timetable_generator_configs c ON c.id = r.config_id
             LEFT JOIN users u ON u.id = r.created_by
             WHERE r.id = ? AND r.institution_id = ?",
            [$runId, $instId]
        );
        $run = $this->db->fetch();

        if (!$run) {
            $this->redirectWith(url('academic/timetable/generator'), 'error', 'Run not found.');
            return;
        }

        $assignments = json_decode($run['result_payload'] ?? '[]', true) ?: [];
        $conflicts   = json_decode($run['conflict_payload'] ?? '[]', true) ?: [];

        // Enrich assignments with names
        $assignments = $this->enrichAssignments($assignments, $instId);
        $conflicts   = $this->enrichConflicts($conflicts, $instId);

        // Load periods for the grid
        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id = ? ORDER BY period_number",
            [$instId]
        );
        $periods = $this->db->fetchAll();

        // Group assignments by section → day → period
        $grid = [];
        foreach ($assignments as $a) {
            $grid[$a['section_id']][$a['day']][$a['period_id']] = $a;
        }

        $this->view('academic/timetable/run_review', compact('run', 'grid', 'periods', 'conflicts', 'assignments'));
    }

    public function approveRun(int $runId): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $instId = $this->institutionId;

        $this->db->query(
            "SELECT * FROM timetable_generator_runs
             WHERE id = ? AND institution_id = ? AND status = 'completed'",
            [$runId, $instId]
        );
        $run = $this->db->fetch();

        if (!$run) {
            $this->redirectWith(url('academic/timetable/generator'), 'error', 'Run not found or not in completed state.');
            return;
        }

        $assignments = json_decode($run['result_payload'] ?? '[]', true) ?: [];
        $sections    = json_decode($run['sections_scope'] ?? '[]', true) ?: [];

        try {
            $this->db->beginTransaction();

            // Clear existing timetable for the sections in scope
            if (!empty($sections)) {
                $ph = implode(',', array_fill(0, count($sections), '?'));
                $this->db->query(
                    "DELETE FROM academic_timetable
                     WHERE institution_id = ? AND section_id IN ({$ph})",
                    array_merge([$instId], $sections)
                );
            }

            // Insert all generated assignments
            foreach ($assignments as $a) {
                $this->db->insert('academic_timetable', [
                    'institution_id' => $instId,
                    'batch_id'       => $a['batch_id'] ?? 0,
                    'section_id'     => $a['section_id'] ?? null,
                    'day_of_week'    => $a['day'],
                    'period_id'      => $a['period_id'],
                    'subject_id'     => $a['subject_id'],
                    'faculty_id'     => $a['faculty_id'],
                    'entry_type'     => $a['entry_type'] ?? 'lecture',
                    'is_active'      => 1,
                    'created_by'     => $this->user['id'],
                ]);
            }

            // Mark run as approved
            $this->db->update('timetable_generator_runs', [
                'status'      => 'approved',
                'approved_by' => $this->user['id'],
                'approved_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$runId]);

            $this->db->commit();
            $this->logAudit('timetable_run_approved', 'timetable_generator_runs', $runId);

        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->redirectWith(url("academic/timetable/generator/run/{$runId}"), 'error', 'Failed to apply timetable: ' . $e->getMessage());
            return;
        }

        $this->redirectWith(url('academic/timetable'), 'success',
            count($assignments) . ' timetable slots applied successfully from run #' . $runId . '.');
    }

    public function discardRun(int $runId): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $this->db->update('timetable_generator_runs', ['status' => 'discarded'], 'id = ? AND institution_id = ?', [$runId, $this->institutionId]);
        $this->redirectWith(url('academic/timetable/generator'), 'success', 'Run discarded.');
    }

    // ── Constraints Management ────────────────────────────────────────────────

    public function saveConstraint(): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }

        $instId = $this->institutionId;
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];

        $this->db->insert('timetable_generator_constraints', [
            'institution_id'  => $instId,
            'config_id'       => (int)($body['config_id'] ?? 0),
            'constraint_key'  => sanitize($body['constraint_key'] ?? ''),
            'constraint_type' => in_array($body['constraint_type'] ?? '', ['hard','soft']) ? $body['constraint_type'] : 'hard',
            'target_type'     => in_array($body['target_type'] ?? '', ['global','faculty','section','subject','room']) ? $body['target_type'] : 'global',
            'target_id'       => !empty($body['target_id']) ? (int)$body['target_id'] : null,
            'value'           => !empty($body['value']) ? json_encode($body['value']) : null,
            'weight'          => max(1, min(10, (int)($body['weight'] ?? 5))),
            'description'     => sanitize($body['description'] ?? ''),
        ]);

        jsonResponse(['success' => true, 'message' => 'Constraint saved.']);
    }

    public function deleteConstraint(int $id): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }

        $this->db->query(
            "DELETE FROM timetable_generator_constraints WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        jsonResponse(['success' => true]);
    }

    // ── Analytics ────────────────────────────────────────────────────────────

    public function analytics(): void
    {
        $this->authorize('timetable.view');

        $instId = $this->institutionId;

        // Faculty load: periods per faculty per week
        $this->db->query(
            "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    COUNT(at.id) AS total_periods,
                    COUNT(DISTINCT at.day_of_week) AS teaching_days,
                    COUNT(DISTINCT at.section_id) AS sections_count,
                    COUNT(DISTINCT at.subject_id) AS subjects_count
             FROM academic_timetable at
             INNER JOIN users u ON u.id = at.faculty_id
             WHERE at.institution_id = ? AND at.is_active = 1
             GROUP BY u.id
             ORDER BY total_periods DESC",
            [$instId]
        );
        $facultyLoad = $this->db->fetchAll();

        // Room utilization
        $this->db->query(
            "SELECT
                COALESCE(cr.room_number, at.room_id) AS room_label,
                cr.room_name,
                cr.capacity,
                COUNT(at.id)            AS total_slots,
                COUNT(DISTINCT at.day_of_week) AS days_used
             FROM academic_timetable at
             LEFT JOIN classrooms cr ON cr.id = at.room_id
             WHERE at.institution_id = ? AND at.is_active = 1 AND at.room_id IS NOT NULL
             GROUP BY at.room_id
             ORDER BY total_slots DESC",
            [$instId]
        );
        $roomUtil = $this->db->fetchAll();

        // Subject distribution per section
        $this->db->query(
            "SELECT sec.name AS section_name, b.name AS batch_name,
                    sub.subject_name, sub.subject_code,
                    COUNT(at.id) AS periods_per_week,
                    at.entry_type
             FROM academic_timetable at
             INNER JOIN academic_sections sec ON sec.id = at.section_id
             INNER JOIN academic_batches b ON b.id = at.batch_id
             INNER JOIN subjects sub ON sub.id = at.subject_id
             WHERE at.institution_id = ? AND at.is_active = 1
             GROUP BY at.section_id, at.subject_id, at.entry_type
             ORDER BY sec.name, sub.subject_name",
            [$instId]
        );
        $subjectDist = $this->db->fetchAll();

        // Per-day load across institution
        $this->db->query(
            "SELECT day_of_week, COUNT(*) AS total_periods, COUNT(DISTINCT section_id) AS active_sections
             FROM academic_timetable
             WHERE institution_id = ? AND is_active = 1
             GROUP BY day_of_week
             ORDER BY FIELD(day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')",
            [$instId]
        );
        $dayLoad = $this->db->fetchAll();

        // Generation run history chart data
        $this->db->query(
            "SELECT DATE(created_at) AS run_date, COUNT(*) AS runs,
                    AVG(score) AS avg_score, MAX(score) AS best_score
             FROM timetable_generator_runs
             WHERE institution_id = ?
             GROUP BY DATE(created_at)
             ORDER BY run_date DESC LIMIT 14",
            [$instId]
        );
        $runHistory = $this->db->fetchAll();

        $this->view('academic/timetable/analytics', compact(
            'facultyLoad', 'roomUtil', 'subjectDist', 'dayLoad', 'runHistory'
        ));
    }

    // ── Teacher Unavailability ────────────────────────────────────────────────

    public function unavailability(): void
    {
        $this->authorize('timetable.manage');

        $instId = $this->institutionId;

        $this->db->query(
            "SELECT u2.*, p.period_name, p.start_time, p.end_time,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM timetable_teacher_unavailability u2
             LEFT JOIN academic_timetable_periods p ON p.id = u2.period_id
             LEFT JOIN users u ON u.id = u2.faculty_id
             WHERE u2.institution_id = ?
             ORDER BY u.first_name, u2.day_of_week",
            [$instId]
        );
        $entries = $this->db->fetchAll();

        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1 ORDER BY u.first_name",
            [$instId]
        );
        $faculty = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id = ? AND is_break = 0 ORDER BY period_number",
            [$instId]
        );
        $periods = $this->db->fetchAll();

        $this->view('academic/timetable/unavailability', compact('entries', 'faculty', 'periods'));
    }

    public function saveUnavailability(): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $instId = $this->institutionId;
        $data   = $this->postData();

        $this->db->insert('timetable_teacher_unavailability', [
            'institution_id' => $instId,
            'faculty_id'     => (int)($data['faculty_id'] ?? 0),
            'day_of_week'    => $data['day_of_week'] ?? 'monday',
            'period_id'      => !empty($data['period_id']) ? (int)$data['period_id'] : null,
            'reason'         => sanitize($data['reason'] ?? ''),
            'effective_from' => !empty($data['effective_from']) ? $data['effective_from'] : null,
            'effective_to'   => !empty($data['effective_to']) ? $data['effective_to'] : null,
            'is_recurring'   => !empty($data['is_recurring']) ? 1 : 0,
            'created_by'     => $this->user['id'],
        ]);

        $this->redirectWith(url('academic/timetable/generator/unavailability'), 'success', 'Unavailability recorded.');
    }

    public function deleteUnavailability(int $id): void
    {
        $this->authorize('timetable.manage');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $this->db->query(
            "DELETE FROM timetable_teacher_unavailability WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->redirectWith(url('academic/timetable/generator/unavailability'), 'success', 'Entry removed.');
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function exportSection(int $sectionId): void
    {
        $this->authorize('timetable.view');

        $instId = $this->institutionId;
        $format = $this->input('format', 'html');

        $this->db->query(
            "SELECT at.*,
                    sub.subject_name, sub.subject_code,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    p.period_name, p.start_time, p.end_time, p.is_break,
                    sec.name AS section_name,
                    b.name  AS batch_name,
                    c.name  AS course_name
             FROM academic_timetable at
             LEFT JOIN subjects sub ON sub.id = at.subject_id
             LEFT JOIN users u ON u.id = at.faculty_id
             LEFT JOIN academic_timetable_periods p ON p.id = at.period_id
             LEFT JOIN academic_sections sec ON sec.id = at.section_id
             LEFT JOIN academic_batches b ON b.id = at.batch_id
             LEFT JOIN courses c ON c.id = b.course_id
             WHERE at.section_id = ? AND at.institution_id = ? AND at.is_active = 1
             ORDER BY FIELD(at.day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday'),
                      p.period_number",
            [$sectionId, $instId]
        );
        $rows = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id = ? ORDER BY period_number",
            [$instId]
        );
        $periods = $this->db->fetchAll();

        if ($format === 'csv') {
            $this->exportCsv($rows, $sectionId);
        } elseif ($format === 'ical') {
            $this->exportIcal($rows, $sectionId);
        } else {
            $this->view('academic/timetable/export_html', compact('rows', 'periods'));
        }
    }

    private function exportCsv(array $rows, int $sectionId): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="timetable_section_' . $sectionId . '_' . date('Ymd') . '.csv"');
        $fh = fopen('php://output', 'w');
        fputcsv($fh, ['Day', 'Period', 'Start', 'End', 'Subject', 'Code', 'Faculty', 'Type']);
        foreach ($rows as $r) {
            fputcsv($fh, [
                ucfirst($r['day_of_week']),
                $r['period_name'],
                $r['start_time'],
                $r['end_time'],
                $r['subject_name'],
                $r['subject_code'],
                $r['faculty_name'],
                $r['entry_type'],
            ]);
        }
        fclose($fh);
        exit;
    }

    private function exportIcal(array $rows, int $sectionId): void
    {
        $dayMap = ['monday'=>'MO','tuesday'=>'TU','wednesday'=>'WE','thursday'=>'TH','friday'=>'FR','saturday'=>'SA','sunday'=>'SU'];
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="timetable_section_' . $sectionId . '.ics"');

        echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//EduMatrix//Timetable//EN\r\n";
        foreach ($rows as $r) {
            $byday = $dayMap[$r['day_of_week']] ?? 'MO';
            $dtstart = date('Ymd') . 'T' . str_replace(':', '', substr($r['start_time'], 0, 5)) . '00';
            $dtend   = date('Ymd') . 'T' . str_replace(':', '', substr($r['end_time'], 0, 5)) . '00';
            echo "BEGIN:VEVENT\r\n";
            echo "SUMMARY:{$r['subject_name']} ({$r['subject_code']})\r\n";
            echo "DESCRIPTION:Faculty: {$r['faculty_name']} | Type: {$r['entry_type']}\r\n";
            echo "DTSTART:{$dtstart}\r\n";
            echo "DTEND:{$dtend}\r\n";
            echo "RRULE:FREQ=WEEKLY;BYDAY={$byday}\r\n";
            echo "UID:" . uniqid('tt_') . "@edumatrix\r\n";
            echo "END:VEVENT\r\n";
        }
        echo "END:VCALENDAR\r\n";
        exit;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function enrichAssignments(array $assignments, int $instId): array
    {
        if (empty($assignments)) return [];

        // Collect unique IDs
        $subjectIds = array_unique(array_column($assignments, 'subject_id'));
        $facultyIds = array_unique(array_filter(array_column($assignments, 'faculty_id')));
        $sectionIds = array_unique(array_column($assignments, 'section_id'));
        $periodIds  = array_unique(array_column($assignments, 'period_id'));

        $subjects = $faculty = $sections = $periods = [];

        if ($subjectIds) {
            $ph = implode(',', array_fill(0, count($subjectIds), '?'));
            $this->db->query("SELECT id, subject_name, subject_code FROM subjects WHERE id IN ($ph)", $subjectIds);
            foreach ($this->db->fetchAll() as $r) $subjects[$r['id']] = $r;
        }
        if ($facultyIds) {
            $ph = implode(',', array_fill(0, count($facultyIds), '?'));
            $this->db->query("SELECT id, first_name, last_name FROM users WHERE id IN ($ph)", $facultyIds);
            foreach ($this->db->fetchAll() as $r) $faculty[$r['id']] = $r;
        }
        if ($sectionIds) {
            $ph = implode(',', array_fill(0, count($sectionIds), '?'));
            $this->db->query("SELECT id, name FROM academic_sections WHERE id IN ($ph)", $sectionIds);
            foreach ($this->db->fetchAll() as $r) $sections[$r['id']] = $r;
        }
        if ($periodIds) {
            $ph = implode(',', array_fill(0, count($periodIds), '?'));
            $this->db->query("SELECT id, period_name, period_number, start_time, end_time FROM academic_timetable_periods WHERE id IN ($ph)", $periodIds);
            foreach ($this->db->fetchAll() as $r) $periods[$r['id']] = $r;
        }

        foreach ($assignments as &$a) {
            $a['subject_name'] = $subjects[$a['subject_id']]['subject_name'] ?? 'Unknown';
            $a['subject_code'] = $subjects[$a['subject_id']]['subject_code'] ?? '';
            $fac = $faculty[$a['faculty_id']] ?? null;
            $a['faculty_name'] = $fac ? trim($fac['first_name'] . ' ' . $fac['last_name']) : '—';
            $a['section_name'] = $sections[$a['section_id']]['name'] ?? 'Unknown';
            $a['period_name']  = $periods[$a['period_id']]['period_name']  ?? 'P?';
            $a['period_number']= $periods[$a['period_id']]['period_number'] ?? 0;
            $a['start_time']   = $periods[$a['period_id']]['start_time']   ?? '';
            $a['end_time']     = $periods[$a['period_id']]['end_time']     ?? '';
        }
        unset($a);

        return $assignments;
    }

    private function enrichConflicts(array $conflicts, int $instId): array
    {
        if (empty($conflicts)) return [];
        $subjectIds = array_unique(array_column($conflicts, 'subject_id'));
        $sectionIds = array_unique(array_column($conflicts, 'section_id'));

        $subjects = $sections = [];
        if ($subjectIds) {
            $ph = implode(',', array_fill(0, count($subjectIds), '?'));
            $this->db->query("SELECT id, subject_name, subject_code FROM subjects WHERE id IN ($ph)", $subjectIds);
            foreach ($this->db->fetchAll() as $r) $subjects[$r['id']] = $r;
        }
        if ($sectionIds) {
            $ph = implode(',', array_fill(0, count($sectionIds), '?'));
            $this->db->query("SELECT id, name FROM academic_sections WHERE id IN ($ph)", $sectionIds);
            foreach ($this->db->fetchAll() as $r) $sections[$r['id']] = $r;
        }
        foreach ($conflicts as &$c) {
            $c['subject_name'] = $subjects[$c['subject_id']]['subject_name'] ?? 'Unknown';
            $c['subject_code'] = $subjects[$c['subject_id']]['subject_code'] ?? '';
            $c['section_name'] = $sections[$c['section_id']]['name'] ?? 'Unknown';
        }
        unset($c);
        return $conflicts;
    }
}
