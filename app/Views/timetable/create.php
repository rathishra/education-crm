<?php $pageTitle = 'Add Period'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Timetable Period</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('timetable') ?>">Timetable</a></li>
                <li class="breadcrumb-item active">Add Period</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-plus me-2 text-primary"></i>Period Details
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('timetable') ?>" id="timetableForm">
                    <?= csrfField() ?>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                            <select class="form-select" name="course_id" id="course_id" required
                                    onchange="this.form.action='<?= url('timetable/create') ?>'; this.form.method='GET'; this.form.submit();">
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                            <select class="form-select" name="batch_id" required>
                                <option value="">-- Select Batch --</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= old('batch_id') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">-- Select Subject --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= old('subject_id') == $s['id'] ? 'selected' : '' ?>>
                                        <?= e($s['name']) ?> (<?= e($s['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                            <select class="form-select" name="faculty_id" required>
                                <option value="">-- Select Faculty --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= old('faculty_id') == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select" name="day_of_week" required>
                                <?php $days = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday','sunday'=>'Sunday']; ?>
                                <?php foreach ($days as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= old('day_of_week') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Start – End Time <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="time" class="form-control" name="start_time" value="<?= e(old('start_time', '09:00')) ?>" required>
                                <span class="input-group-text">→</span>
                                <input type="time" class="form-control" name="end_time" value="<?= e(old('end_time', '10:00')) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room / Lab</label>
                            <input type="text" class="form-control" name="room_number" value="<?= e(old('room_number')) ?>" placeholder="e.g. Room 101">
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save Period
                            </button>
                            <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
