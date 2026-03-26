<?php $pageTitle = 'Edit Subject'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Subject</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('subjects') ?>">Subjects</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('subjects') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url("subjects/{$subject['id']}") ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Department</label>
                    <select class="form-select" name="department_id" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= old('department_id', $subject['department_id']) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6"></div>

                <div class="col-md-3">
                    <label class="form-label required">Subject Code</label>
                    <input type="text" class="form-control" name="code" value="<?= e(old('code', $subject['code'])) ?>" required>
                </div>

                <div class="col-md-9">
                    <label class="form-label required">Subject Name</label>
                    <input type="text" class="form-control" name="name" value="<?= e(old('name', $subject['name'])) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Subject Type</label>
                    <select class="form-select" name="type" required>
                        <option value="theory" <?= old('type', $subject['type']) == 'theory' ? 'selected' : '' ?>>Theory</option>
                        <option value="practical" <?= old('type', $subject['type']) == 'practical' ? 'selected' : '' ?>>Practical</option>
                        <option value="project" <?= old('type', $subject['type']) == 'project' ? 'selected' : '' ?>>Project</option>
                        <option value="extra_curricular" <?= old('type', $subject['type']) == 'extra_curricular' ? 'selected' : '' ?>>Extra Curricular</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Credits</label>
                    <input type="number" step="0.5" min="0" class="form-control" name="credits" value="<?= e(old('credits', $subject['credits'])) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="active" <?= old('status', $subject['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $subject['status']) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Subject</button>
                    <a href="<?= url('subjects') ?>" class="btn btn-light ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
