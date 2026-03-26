<?php $pageTitle = 'Edit Timetable Period'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Timetable Period</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('timetable') ?>">Timetable</a></li>
                <li class="breadcrumb-item active">Edit Period</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('timetable/' . $period['id']) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="course_id" value="<?= e($period['course_id'] ?? '') ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Batch</label>
                    <select class="form-select" name="batch_id" required>
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $period['batch_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Subject</label>
                    <select class="form-select" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $period['subject_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Faculty</label>
                    <select class="form-select" name="faculty_id" required>
                        <option value="">-- Select Faculty --</option>
                        <?php foreach ($faculties as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= $period['faculty_id'] == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Day of Week</label>
                    <select class="form-select" name="day_of_week" required>
                        <?php foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $d): ?>
                        <option value="<?= $d ?>" <?= $period['day_of_week'] === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Start Time</label>
                    <input type="time" class="form-control" name="start_time" value="<?= e($period['start_time']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">End Time</label>
                    <input type="time" class="form-control" name="end_time" value="<?= e($period['end_time']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Room Number</label>
                    <input type="text" class="form-control" name="room_number" value="<?= e($period['room_number'] ?? '') ?>">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Period</button>
                    <a href="<?= url('timetable') ?>" class="btn btn-light ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
