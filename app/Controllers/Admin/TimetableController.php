<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TimetableController extends BaseController
{
    public function index(): void
    {
        $this->authorize('timetable.view');

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $courseId = $this->input('course_id');
        $batches  = [];
        if ($courseId) {
            $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$courseId]);
            $batches = $this->db->fetchAll();
        }

        $batchId   = $this->input('batch_id');
        $timetable = [];

        if ($batchId) {
            $sql = "SELECT t.*, s.name as subject_name, s.code as subject_code,
                           CONCAT(u.first_name, ' ', COALESCE(u.last_name,'')) as faculty_name
                    FROM timetables t
                    JOIN subjects s ON s.id = t.subject_id
                    JOIN users u ON u.id = t.faculty_id
                    WHERE t.batch_id = ? AND t.institution_id = ?
                    ORDER BY t.day_of_week, t.start_time ASC";
            $records = $this->db->query($sql, [$batchId, $this->institutionId])->fetchAll();

            foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
                $timetable[$day] = [];
            }
            foreach ($records as $r) {
                $timetable[$r['day_of_week']][] = $r;
            }
        }

        $this->view('timetable/index', compact('courses', 'batches', 'courseId', 'batchId', 'timetable'));
    }

    public function create(): void
    {
        $this->authorize('timetable.manage');

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $sql = "SELECT u.id, CONCAT(u.first_name, ' ', COALESCE(u.last_name,'')) as name
                FROM users u
                JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
                WHERE u.status = 'active'
                GROUP BY u.id
                ORDER BY u.first_name";
        $faculties = $this->db->query($sql, [$this->institutionId])->fetchAll();

        $courseId = $this->input('course_id');
        $batches  = [];
        if ($courseId) {
            $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$courseId]);
            $batches = $this->db->fetchAll();
        }

        $this->view('timetable/create', compact('courses', 'batches', 'subjects', 'faculties', 'courseId'));
    }

    public function store(): void
    {
        $this->authorize('timetable.manage');

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'batch_id'    => 'required',
            'subject_id'  => 'required',
            'faculty_id'  => 'required',
            'day_of_week' => 'required',
            'start_time'  => 'required',
            'end_time'    => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Check for time conflicts on same batch + day
        $this->db->query(
            "SELECT id FROM timetables
             WHERE batch_id = ? AND day_of_week = ?
               AND ((start_time < ? AND end_time > ?) OR (start_time >= ? AND start_time < ?))",
            [$data['batch_id'], $data['day_of_week'],
             $data['end_time'], $data['start_time'],
             $data['start_time'], $data['end_time']]
        );
        if ($this->db->fetch()) {
            $this->backWithErrors(['A timetable conflict exists for this batch on the selected day and time.']);
            return;
        }

        $id = $this->db->insert('timetables', [
            'institution_id' => $this->institutionId,
            'batch_id'       => (int)$data['batch_id'],
            'subject_id'     => (int)$data['subject_id'],
            'faculty_id'     => (int)$data['faculty_id'],
            'day_of_week'    => $data['day_of_week'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'room_number'    => sanitize($data['room_number'] ?? ''),
        ]);

        $this->logAudit('timetable_period_added', 'timetable', $id);
        $this->redirectWith(
            url('timetable?course_id=' . $data['course_id'] . '&batch_id=' . $data['batch_id']),
            'success',
            'Timetable period added successfully.'
        );
    }

    public function edit(int $id): void
    {
        $this->authorize('timetable.manage');

        $this->db->query("SELECT t.*, s.name as subject_name, b.course_id FROM timetables t JOIN subjects s ON s.id = t.subject_id JOIN batches b ON b.id = t.batch_id WHERE t.id = ? AND t.institution_id = ?", [$id, $this->institutionId]);
        $period = $this->db->fetch();
        if (!$period) {
            $this->redirectWith(url('timetable'), 'error', 'Timetable period not found.');
            return;
        }

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$period['course_id']]);
        $batches = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $sql = "SELECT u.id, CONCAT(u.first_name, ' ', COALESCE(u.last_name,'')) as name
                FROM users u JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
                WHERE u.status = 'active' GROUP BY u.id ORDER BY u.first_name";
        $faculties = $this->db->query($sql, [$this->institutionId])->fetchAll();

        $this->view('timetable/edit', compact('period', 'courses', 'batches', 'subjects', 'faculties'));
    }

    public function update(int $id): void
    {
        $this->authorize('timetable.manage');

        $this->db->query("SELECT id, batch_id FROM timetables WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $period = $this->db->fetch();
        if (!$period) {
            $this->redirectWith(url('timetable'), 'error', 'Timetable period not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'batch_id'    => 'required',
            'subject_id'  => 'required',
            'faculty_id'  => 'required',
            'day_of_week' => 'required',
            'start_time'  => 'required',
            'end_time'    => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $this->db->update('timetables', [
            'batch_id'    => (int)$data['batch_id'],
            'subject_id'  => (int)$data['subject_id'],
            'faculty_id'  => (int)$data['faculty_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'room_number' => sanitize($data['room_number'] ?? ''),
        ], '`id` = ?', [$id]);

        $this->logAudit('timetable_period_updated', 'timetable', $id);
        $this->redirectWith(
            url('timetable?course_id=' . $data['course_id'] . '&batch_id=' . $data['batch_id']),
            'success',
            'Timetable period updated successfully.'
        );
    }

    public function destroy(int $id): void
    {
        $this->authorize('timetable.manage');

        $this->db->query("SELECT batch_id FROM timetables WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $record = $this->db->fetch();
        if (!$record) {
            $this->redirectWith(url('timetable'), 'error', 'Timetable period not found.');
            return;
        }

        $this->db->query("DELETE FROM timetables WHERE id = ?", [$id]);
        $this->logAudit('timetable_period_deleted', 'timetable', $id);
        $this->redirectWith(url('timetable'), 'success', 'Timetable period deleted.');
    }
}
