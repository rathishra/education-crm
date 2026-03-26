<?php $pageTitle = 'Edit Academic Year'; ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Academic Year</h6>
                <a href="<?= url('academic-years') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('academic-years/' . $ay['id']) ?>">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Institution</label>
                        <input type="text" class="form-control" value="<?= e($ay['institution_name'] ?? 'Assigned Institution') ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= old('name', $ay['name']) ?>" required maxlength="50">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= old('start_date', $ay['start_date']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= old('end_date', $ay['end_date']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" <?= old('status', $ay['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', $ay['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" id="isCurrent" <?= old('is_current', $ay['is_current']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isCurrent">Set as Current Academic Year</label>
                        </div>
                        <div class="form-text">If checked, all other academic years for this institution will be unset as current.</div>
                    </div>

                    <hr>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Academic Year
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php // End of content ?>
