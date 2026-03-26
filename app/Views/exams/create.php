<?php $pageTitle = 'Add Exam'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Add Exam</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('exams') ?>">Exams</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('exams') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('exams') ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Exam Name</label>
                    <input type="text" class="form-control" name="name" value="<?= e(old('name')) ?>" placeholder="e.g., Mid-Term 2026" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Exam Type</label>
                    <select class="form-select" name="type" required>
                        <option value="internal" <?= old('type') == 'internal' ? 'selected' : '' ?>>Internal</option>
                        <option value="semester" <?= old('type') == 'semester' ? 'selected' : '' ?>>Semester End</option>
                        <option value="practical" <?= old('type') == 'practical' ? 'selected' : '' ?>>Practical</option>
                        <option value="entrance" <?= old('type') == 'entrance' ? 'selected' : '' ?>>Entrance</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?= e(old('start_date')) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?= e(old('end_date')) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="upcoming" <?= old('status') == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="ongoing" <?= old('status') == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Exam</button>
                    <a href="<?= url('exams') ?>" class="btn btn-light ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
