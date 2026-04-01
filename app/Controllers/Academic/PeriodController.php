<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class PeriodController extends BaseController
{
    private function ensureSchema(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS academic_timetable_periods (
                id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                institution_id INT UNSIGNED NOT NULL,
                period_number  INT NOT NULL DEFAULT 1,
                period_name    VARCHAR(60) NOT NULL,
                start_time     TIME NOT NULL,
                end_time       TIME NOT NULL,
                is_break       TINYINT(1) NOT NULL DEFAULT 0,
                break_name     VARCHAR(60) NULL,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_inst (institution_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    // ─── INDEX ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->ensureSchema();

        $this->db->query(
            "SELECT * FROM academic_timetable_periods
             WHERE institution_id = ? ORDER BY period_number ASC",
            [$this->institutionId]
        );
        $periods = $this->db->fetchAll();

        $totalPeriods = count(array_filter($periods, fn($p) => !(int)($p['is_break'] ?? 0)));
        $totalBreaks  = count(array_filter($periods, fn($p) => (int)($p['is_break'] ?? 0)));

        // Calculate total teaching time in minutes
        $teachingMins = 0;
        foreach ($periods as $p) {
            if (!(int)($p['is_break'] ?? 0)) {
                [$sh, $sm] = explode(':', $p['start_time']);
                [$eh, $em] = explode(':', $p['end_time']);
                $teachingMins += ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
            }
        }

        $this->view('academic/periods/index', compact(
            'periods', 'totalPeriods', 'totalBreaks', 'teachingMins'
        ));
    }

    // ─── STORE (AJAX) ──────────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $period_number = (int)($_POST['period_number'] ?? 0);
        $period_name   = trim($_POST['period_name']   ?? '');
        $start_time    = trim($_POST['start_time']    ?? '');
        $end_time      = trim($_POST['end_time']      ?? '');
        $is_break      = isset($_POST['is_break']) ? 1 : 0;
        $break_name    = trim($_POST['break_name']    ?? '') ?: null;

        $errors = [];
        if ($period_number < 1)  $errors[] = 'Period number must be ≥ 1.';
        if (empty($period_name)) $errors[] = 'Period name is required.';
        if (empty($start_time))  $errors[] = 'Start time is required.';
        if (empty($end_time))    $errors[] = 'End time is required.';
        if ($start_time && $end_time && $start_time >= $end_time) $errors[] = 'End time must be after start time.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM academic_timetable_periods WHERE institution_id = ? AND period_number = ?",
                [$this->institutionId, $period_number]
            );
            if ($this->db->fetch()) {
                $errors[] = "Period number $period_number already exists.";
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        try {
            $this->db->insert('academic_timetable_periods', [
                'institution_id' => $this->institutionId,
                'period_number'  => $period_number,
                'period_name'    => $period_name,
                'start_time'     => $start_time,
                'end_time'       => $end_time,
                'is_break'       => $is_break,
                'break_name'     => $break_name,
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Period added.', 'id' => $this->db->lastInsertId()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── GET ONE (AJAX) ────────────────────────────────────────
    public function getOne(int $id): void
    {
        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $period = $this->db->fetch();
        if (!$period) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $period]);
        exit;
    }

    // ─── UPDATE (AJAX POST) ────────────────────────────────────
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $this->db->query(
            "SELECT id FROM academic_timetable_periods WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }

        $period_number = (int)($_POST['period_number'] ?? 0);
        $period_name   = trim($_POST['period_name']   ?? '');
        $start_time    = trim($_POST['start_time']    ?? '');
        $end_time      = trim($_POST['end_time']      ?? '');
        $is_break      = isset($_POST['is_break']) ? 1 : 0;
        $break_name    = trim($_POST['break_name']    ?? '') ?: null;

        $errors = [];
        if ($period_number < 1)  $errors[] = 'Period number must be ≥ 1.';
        if (empty($period_name)) $errors[] = 'Period name is required.';
        if (empty($start_time))  $errors[] = 'Start time is required.';
        if (empty($end_time))    $errors[] = 'End time is required.';
        if ($start_time && $end_time && $start_time >= $end_time) $errors[] = 'End time must be after start time.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM academic_timetable_periods WHERE institution_id = ? AND period_number = ? AND id != ?",
                [$this->institutionId, $period_number, $id]
            );
            if ($this->db->fetch()) {
                $errors[] = "Period number $period_number already exists.";
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        try {
            $this->db->query(
                "UPDATE academic_timetable_periods
                 SET period_number=?, period_name=?, start_time=?, end_time=?, is_break=?, break_name=?
                 WHERE id=? AND institution_id=?",
                [$period_number, $period_name, $start_time, $end_time, $is_break, $break_name, $id, $this->institutionId]
            );
            echo json_encode(['status' => 'success', 'message' => 'Period updated.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── DESTROY (AJAX POST) ───────────────────────────────────
    public function destroy(int $id): void
    {
        // Check timetable usage
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM academic_timetable WHERE period_id = ? AND institution_id = ?",
                [$id, $this->institutionId]
            );
            $used = ($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) {
            $used = 0;
        }

        if ($used > 0) {
            echo json_encode(['status' => 'error', 'message' => "Cannot delete: period used in $used timetable slot(s)."]);
            exit;
        }

        $this->db->query(
            "DELETE FROM academic_timetable_periods WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        echo json_encode(['status' => 'success', 'message' => 'Period deleted.']);
        exit;
    }

    // ─── SEED DEFAULTS (AJAX POST) ────────────────────────────
    public function seedDefaults(): void
    {
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM academic_timetable_periods WHERE institution_id = ?",
            [$this->institutionId]
        );
        if (($this->db->fetch()['cnt'] ?? 0) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Periods already exist. Clear first.']);
            exit;
        }

        $defaults = [
            [1, 'Period 1', '09:00', '09:50', 0, null],
            [2, 'Period 2', '09:50', '10:40', 0, null],
            [3, 'Break',    '10:40', '11:00', 1, 'Short Break'],
            [4, 'Period 3', '11:00', '11:50', 0, null],
            [5, 'Period 4', '11:50', '12:40', 0, null],
            [6, 'Lunch',    '12:40', '13:30', 1, 'Lunch Break'],
            [7, 'Period 5', '13:30', '14:20', 0, null],
            [8, 'Period 6', '14:20', '15:10', 0, null],
        ];

        foreach ($defaults as $p) {
            $this->db->insert('academic_timetable_periods', [
                'institution_id' => $this->institutionId,
                'period_number'  => $p[0],
                'period_name'    => $p[1],
                'start_time'     => $p[2],
                'end_time'       => $p[3],
                'is_break'       => $p[4],
                'break_name'     => $p[5],
            ]);
        }
        echo json_encode(['status' => 'success', 'message' => '8 default periods created.', 'count' => 8]);
        exit;
    }

    // ─── CLEAR ALL (AJAX POST) ────────────────────────────────
    public function clearAll(): void
    {
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM academic_timetable WHERE institution_id = ?",
                [$this->institutionId]
            );
            $ttUsed = ($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) { $ttUsed = 0; }

        if ($ttUsed > 0) {
            echo json_encode(['status' => 'error', 'message' => "Cannot clear: $ttUsed timetable slot(s) exist. Clear timetable first."]);
            exit;
        }

        $this->db->query(
            "DELETE FROM academic_timetable_periods WHERE institution_id = ?",
            [$this->institutionId]
        );
        echo json_encode(['status' => 'success', 'message' => 'All periods cleared.']);
        exit;
    }

}
