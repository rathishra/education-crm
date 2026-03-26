<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TimetableController extends BaseController
{
    public function index(): void
    {
        $this->authorize('timetable.view');

        $institutionId = session('institution_id');
        $courses = [];
        $batches = [];
        
        if ($institutionId) {
            $courses = db()->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$institutionId])->fetchAll();
        }

        $courseId = $this->input('course_id');
        if ($courseId) {
            $batches = db()->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' ORDER BY name", [$courseId])->fetchAll();
        }

        $batchId = $this->input('batch_id');
        $timetable = [];

        if ($batchId) {
            $sql = "SELECT t.*, s.name as subject_name, s.code as subject_code, 
                           CONCAT(u.first_name, ' ', u.last_name) as faculty_name 
                    FROM timetables t
                    JOIN subjects s ON s.id = t.subject_id
                    JOIN users u ON u.id = t.faculty_id
                    WHERE t.batch_id = ? AND t.institution_id = ?
                    ORDER BY t.start_time ASC";
            $records = db()->query($sql, [$batchId, $institutionId])->fetchAll();

            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            foreach ($days as $day) {
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

        $institutionId = session('institution_id');
        $courses = [];
        $batches = [];
        $subjects = [];
        $faculties = [];
        
        if ($institutionId) {
            $courses = db()->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$institutionId])->fetchAll();
            $subjects = db()->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [$institutionId])->fetchAll();
            
            // Assuming faculty Role ID is 7, or users assigned to this institution. We'll fetch users linked to this institution with active status
            // Depending on architecture, we'll fetch users with institution access
            $sql = "SELECT u.id, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as name 
                    FROM users u 
                    JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
                    WHERE u.status = 'active' 
                    GROUP BY u.id 
                    ORDER BY u.first_name";
            $faculties = db()->query($sql, [$institutionId])->fetchAll();
        }

        $courseId = $this->input('course_id');
        if ($courseId) {
            $batches = db()->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' ORDER BY name", [$courseId])->fetchAll();
        }

        $this->view('timetable/create', compact('courses', 'batches', 'subjects', 'faculties', 'courseId'));
    }

    public function store(): void
    {
        $this->authorize('timetable.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'batch_id' => 'required',
            'subject_id' => 'required',
            'faculty_id' => 'required',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('timetables', [
            'institution_id' => session('institution_id'),
            'batch_id'       => $data['batch_id'],
            'subject_id'     => $data['subject_id'],
            'faculty_id'     => $data['faculty_id'],
            'day_of_week'    => $data['day_of_week'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'room_number'    => sanitize($data['room_number'] ?? '')
        ]);

        $this->logAudit('timetable_period_added', 'timetable', $id);
        $this->redirectWith('timetable?course_id=' . $data['course_id'] . '&batch_id=' . $data['batch_id'], 'Timetable period added.', 'success');
    }

    public function edit(int $id): void
    {
        // For simplicity, we can load a form via ajax later or redirect to edit
    }

    public function update(int $id): void
    {
        // Update logic
    }

    public function destroy(int $id): void
    {
        $this->authorize('timetable.manage');
        
        $record = db()->query("SELECT batch_id FROM timetables WHERE id = ? AND institution_id = ?", [$id, session('institution_id')])->fetch();
        if ($record) {
            db()->query("DELETE FROM timetables WHERE id = ?", [$id]);
            $this->logAudit('timetable_period_deleted', 'timetable', $id);
            $this->backWithSuccess('Timetable period deleted.');
        } else {
            $this->backWithErrors(['error' => 'Record not found.']);
        }
    }
}
