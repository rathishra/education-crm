<?php $pageTitle = 'Edit Batch'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Batch</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('batches') ?>">Batches</a></li>
                <li class="breadcrumb-item active"><?= e($batch['name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('batches') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('batches/' . $batch['id']) ?>">
    <?= csrfField() ?>
    <input type="hidden" name="_method" value="PUT">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-layer-group me-2"></i>Batch Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label required">Course</label>
                            <select class="form-select select2" name="course_id" required>
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= old('course_id', $batch['course_id']) == $course['id'] ? 'selected' : '' ?>>
                                    <?= e($course['name']) ?> (<?= e($course['code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label required">Batch Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name', $batch['name'])) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batch Code</label>
                            <input type="text" class="form-control" name="code" value="<?= e(old('code', $batch['code'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="<?= e(old('start_date', $batch['start_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?= e(old('end_date', $batch['end_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="<?= e(old('capacity', $batch['capacity'] ?? '')) ?>" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Timing</label>
                            <input type="text" class="form-control" name="timing" value="<?= e(old('timing', $batch['timing'] ?? '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"><?= e(old('description', $batch['description'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-toggle-on me-2"></i>Status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Batch Status</label>
                        <select class="form-select" name="status">
                            <?php foreach (['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= old('status', $batch['status']) === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-muted small">
                        <div>Created: <?= formatDate($batch['created_at'] ?? '') ?></div>
                        <?php if (!empty($batch['updated_at'])): ?>
                        <div>Updated: <?= timeAgo($batch['updated_at']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Batch</button>
                </div>
            </div>
        </div>
    </div>
</form>
