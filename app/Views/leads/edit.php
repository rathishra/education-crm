<?php $pageTitle = 'Edit Lead - ' . e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Lead</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads/' . $lead['id']) ?>"><?= e($lead['lead_number']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('leads/' . $lead['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('leads/' . $lead['id']) ?>">
    <?= csrfField() ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <!-- Personal Info -->
            <div class="card">
                <div class="card-header"><i class="fas fa-user me-2"></i>Personal Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="<?= e(old('first_name') ?: $lead['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?= e(old('last_name') ?: ($lead['last_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= e(old('phone') ?: $lead['phone']) ?>" placeholder="10-digit" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" class="form-control" name="alternate_phone" value="<?= e(old('alternate_phone') ?: ($lead['alternate_phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e(old('email') ?: ($lead['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?= e(old('date_of_birth') ?: ($lead['date_of_birth'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <?php $gender = old('gender') ?: ($lead['gender'] ?? ''); ?>
                            <select class="form-select" name="gender">
                                <option value="">Select</option>
                                <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $gender === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="card">
                <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Address</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" value="<?= e(old('address_line1') ?: ($lead['address_line1'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" value="<?= e(old('address_line2') ?: ($lead['address_line2'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="<?= e(old('city') ?: ($lead['city'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" value="<?= e(old('state') ?: ($lead['state'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= e(old('pincode') ?: ($lead['pincode'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" value="<?= e(old('country') ?: ($lead['country'] ?? 'India')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Info -->
            <div class="card">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Academic Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Last Qualification</label>
                            <input type="text" class="form-control" name="qualification" value="<?= e(old('qualification') ?: ($lead['qualification'] ?? '')) ?>" placeholder="e.g. 12th, B.Sc, Diploma">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percentage / CGPA</label>
                            <input type="number" class="form-control" name="percentage" value="<?= e(old('percentage') ?: ($lead['percentage'] ?? '')) ?>" step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year of Passing</label>
                            <input type="number" class="form-control" name="passing_year" value="<?= e(old('passing_year') ?: ($lead['passing_year'] ?? '')) ?>" min="2000" max="<?= date('Y') + 1 ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">School / College Name</label>
                            <input type="text" class="form-control" name="school_college" value="<?= e(old('school_college') ?: ($lead['school_college'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-xl-4">
            <!-- Lead Settings -->
            <div class="card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Lead Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Course Interested</label>
                        <?php $courseId = old('course_interested_id') ?: ($lead['course_interested_id'] ?? ''); ?>
                        <select class="form-select select2" name="course_interested_id">
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lead Source</label>
                        <?php $sourceId = old('lead_source_id') ?: ($lead['lead_source_id'] ?? ''); ?>
                        <select class="form-select" name="lead_source_id">
                            <option value="">Select Source</option>
                            <?php foreach ($sources as $src): ?>
                            <option value="<?= $src['id'] ?>" <?= $sourceId == $src['id'] ? 'selected' : '' ?>>
                                <?= e($src['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <?php $statusId = old('lead_status_id') ?: ($lead['lead_status_id'] ?? ''); ?>
                        <select class="form-select" name="lead_status_id">
                            <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= $statusId == $st['id'] ? 'selected' : '' ?>>
                                <?= e($st['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <?php $assignedTo = old('assigned_to') ?: ($lead['assigned_to'] ?? ''); ?>
                        <select class="form-select select2" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $assignedTo == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <?php $priority = old('priority') ?: ($lead['priority'] ?? 'medium'); ?>
                        <select class="form-select" name="priority">
                            <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="urgent" <?= $priority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="4"><?= e(old('notes') ?: ($lead['notes'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Lead Info -->
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">
                        <div><strong>Lead #:</strong> <?= e($lead['lead_number']) ?></div>
                        <div><strong>Created:</strong> <?= formatDate($lead['created_at']) ?></div>
                        <?php if (!empty($lead['updated_at'])): ?>
                        <div><strong>Updated:</strong> <?= formatDate($lead['updated_at']) ?></div>
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Update Lead</button>
                <a href="<?= url('leads/' . $lead['id']) ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
