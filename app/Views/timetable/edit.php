<?php $pageTitle = 'Edit Period'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Timetable Period</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('timetable') ?>">Timetable</a></li>
                <li class="breadcrumb-item active">Edit Period</li>
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
                <i class="fas fa-calendar-edit me-2 text-primary"></i>Period Details
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('timetable/' . $period['id']) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="course_id" value="<?= e($period['course_id'] ?? '') ?>">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                            <select class="form-select" name="batch_id" required>
                                <option value="">-- Select Batch --</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= $period['batch_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">-- Select Subject --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $period['subject_id'] == $s['id'] ? 'selected' : '' ?>>
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
                                    <option value="<?= $f['id'] ?>" <?= $period['faculty_id'] == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select" name="day_of_week" required>
                                <?php foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $d): ?>
                                    <option value="<?= $d ?>" <?= $period['day_of_week'] === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" value="<?= e($period['start_time']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" value="<?= e($period['end_time']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room / Lab</label>
                            <input type="text" class="form-control" name="room_number" value="<?= e($period['room_number'] ?? '') ?>" placeholder="e.g. Room 101">
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Update Period
                            </button>
                            <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
