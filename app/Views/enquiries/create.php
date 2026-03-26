<?php $pageTitle = 'New Enquiry'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus-circle me-2"></i>New Enquiry</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('enquiries') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-user me-2"></i>Enquiry Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" value="<?= e(old('first_name')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?= e(old('last_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" value="<?= e(old('phone')) ?>" placeholder="10-digit mobile" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course Interested</label>
                            <select class="form-select" name="course_interested_id">
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_interested_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Enquiry Source</label>
                            <select class="form-select" name="source_id">
                                <option value="">Select Source</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>" <?= old('source_id') == $src['id'] ? 'selected' : '' ?>><?= e($src['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message / Query</label>
                            <textarea class="form-control" name="message" rows="4" placeholder="What would the enquirer like to know?"><?= e(old('message')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Quick Tips</div>
                <div class="card-body text-muted small">
                    <ul class="ps-3 mb-0">
                        <li>Enter the enquirer's contact details carefully.</li>
                        <li>Select the course they are interested in.</li>
                        <li>Add any message or query they have.</li>
                        <li>You can convert this enquiry to a lead later.</li>
                    </ul>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Save Enquiry</button>
                <a href="<?= url('enquiries') ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
