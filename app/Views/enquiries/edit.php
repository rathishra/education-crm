<?php $pageTitle = 'Edit Enquiry - ' . e($enquiry['enquiry_number']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Enquiry</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('enquiries/' . $enquiry['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('enquiries/' . $enquiry['id']) ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-user me-2"></i>Enquiry Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name"
                                   value="<?= e(old('first_name') ?: $enquiry['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name') ?: ($enquiry['last_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone"
                                   value="<?= e(old('phone') ?: $enquiry['phone']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= e(old('email') ?: ($enquiry['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course Interested</label>
                            <?php $courseId = old('course_interested_id') ?: ($enquiry['course_interested_id'] ?? ''); ?>
                            <select class="form-select" name="course_interested_id">
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <?php
                            // Match current source string to source id
                            $currentSource = $enquiry['source'] ?? '';
                            ?>
                            <select class="form-select" name="source_id">
                                <option value="">Select Source</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"
                                    <?= (old('source_id') == $src['id'] || $currentSource === $src['name']) ? 'selected' : '' ?>>
                                    <?= e($src['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message / Query</label>
                            <textarea class="form-control" name="message" rows="4"><?= e(old('message') ?: ($enquiry['message'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <?php $status = old('status') ?: $enquiry['status']; ?>
                        <select class="form-select" name="status">
                            <?php foreach (['new','contacted','closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Update Enquiry</button>
                <a href="<?= url('enquiries/' . $enquiry['id']) ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
