<?php $pageTitle = 'Academic Years'; ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Academic Years</h6>
        <?php if (hasPermission('academic_years.create')): ?>
        <a href="<?= url('academic-years/create') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Academic Year
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="<?= url('academic-years') ?>" class="row g-3 mb-4">
            <?php if (hasRole('super_admin') || hasRole('org_admin')): ?>
            <div class="col-md-4">
                <select name="institution_id" class="form-select">
                    <option value="">All Institutions</option>
                    <?php foreach ($institutions as $inst): ?>
                        <option value="<?= $inst['id'] ?>" <?= $filters['institution_id'] == $inst['id'] ? 'selected' : '' ?>>
                            <?= e($inst['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Institution</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Current</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($academicYears['data'])): ?>
                        <?php foreach ($academicYears['data'] as $ay): ?>
                            <tr>
                                <td><?= $ay['id'] ?></td>
                                <td><?= e($ay['name']) ?></td>
                                <td><?= e($ay['institution_name']) ?></td>
                                <td><?= formatDate($ay['start_date']) ?></td>
                                <td><?= formatDate($ay['end_date']) ?></td>
                                <td>
                                    <?php if ($ay['is_current']): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ay['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (hasPermission('academic_years.edit')): ?>
                                        <a href="<?= url('academic-years/' . $ay['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('academic_years.delete')): ?>
                                        <form method="POST" action="<?= url('academic-years/' . $ay['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this academic year?')">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No academic years found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $academicYears['links'] ?? '' ?>
    </div>
</div>
<?php // End of content ?>
