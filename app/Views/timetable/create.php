<?php $pageTitle = 'Add Period'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Add Timetable Period</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('timetable') ?>">Timetable</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('timetable') ?>" id="timetableForm">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Course</label>
                    <select class="form-select" name="course_id" id="course_id" required onchange="document.getElementById('timetableForm').action='<?= url('timetable/create') ?>'; document.getElementById('timetableForm').method='GET'; document.getElementById('timetableForm').submit();">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required">Batch</label>
                    <select class="form-select" name="batch_id" required>
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= old('batch_id') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Subject</label>
                    <select class="form-select" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= old('subject_id') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Faculty</label>
                    <select class="form-select" name="faculty_id" required>
                        <option value="">-- Select Faculty --</option>
                        <?php foreach ($faculties as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= old('faculty_id') == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Day of Week</label>
                    <select class="form-select" name="day_of_week" required>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Time</label>
                    <div class="input-group">
                        <input type="time" class="form-control" name="start_time" value="<?= e(old('start_time')) ?>" required>
                        <span class="input-group-text">to</span>
                        <input type="time" class="form-control" name="end_time" value="<?= e(old('end_time')) ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Room Number</label>
                    <input type="text" class="form-control" name="room_number" value="<?= e(old('room_number')) ?>">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Period</button>
                    <a href="<?= url('timetable') ?>" class="btn btn-light ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
