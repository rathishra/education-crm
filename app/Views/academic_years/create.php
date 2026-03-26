<?php $pageTitle = 'Add Academic Year'; ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Add Academic Year</h6>
                <a href="<?= url('academic-years') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('academic-years') ?>">
                    <?= csrfField() ?>

                    <?php if (hasRole('super_admin') || hasRole('org_admin')): ?>
                    <div class="mb-3">
                        <label class="form-label required">Institution</label>
                        <select name="institution_id" class="form-select" required>
                            <option value="">Select Institution</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>" <?= old('institution_id') == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="institution_id" value="<?= currentInstitutionId() ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= old('name') ?>" placeholder="e.g. 2025-26" required maxlength="50">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= old('start_date') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= old('end_date') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" id="isCurrent" <?= old('is_current') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isCurrent">Set as Current Academic Year</label>
                        </div>
                        <div class="form-text">If checked, all other academic years for this institution will be unset as current.</div>
                    </div>

                    <hr>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Academic Year
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php // End of content ?>
