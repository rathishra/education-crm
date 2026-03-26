<?php $pageTitle = 'Enquiry Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-question-circle me-2"></i>Enquiry Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Enquiries</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('enquiries.create')): ?>
    <a href="<?= url('enquiries/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>New Enquiry
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, phone, email..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (['new','contacted','converted','closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="course_id">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($filters['date_from'] ?? '') ?>" title="From date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($filters['date_to'] ?? '') ?>" title="To date">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Enquiries Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($enquiries['total'] ?? 0) ?></strong> enquiries</span>
        <a href="<?= url('enquiries') ?>" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Enquiry #</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Course</th>
                        <th>Handled By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enquiries['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No enquiries found</td></tr>
                    <?php else: ?>
                    <?php foreach ($enquiries['data'] as $enq): ?>
                    <tr>
                        <td><a href="<?= url('enquiries/' . $enq['id']) ?>" class="text-decoration-none"><code><?= e($enq['enquiry_number']) ?></code></a></td>
                        <td><a href="<?= url('enquiries/' . $enq['id']) ?>" class="fw-semibold"><?= e(trim($enq['first_name'] . ' ' . ($enq['last_name'] ?? ''))) ?></a></td>
                        <td>
                            <div><?= e($enq['phone']) ?></div>
                            <small class="text-muted"><?= e($enq['email'] ?? '') ?></small>
                        </td>
                        <td><small><?= e($enq['course_name'] ?? '-') ?></small></td>
                        <td><small><?= e($enq['assigned_to_name'] ?? '-') ?></small></td>
                        <td>
                            <?php
                            $statusColors = ['new' => 'primary', 'contacted' => 'info', 'interested' => 'warning', 'converted' => 'success', 'closed' => 'secondary'];
                            $color = $statusColors[$enq['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>"><?= ucfirst($enq['status']) ?></span>
                        </td>
                        <td><small class="text-muted"><?= formatDate($enq['created_at']) ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('enquiries/' . $enq['id']) ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('enquiries.edit') && $enq['status'] !== 'converted'): ?>
                                <a href="<?= url('enquiries/' . $enq['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.convert') && $enq['status'] !== 'converted'): ?>
                                <form method="POST" action="<?= url('enquiries/' . $enq['id'] . '/convert') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-success" title="Convert to Lead"
                                            onclick="return confirm('Convert this enquiry to a lead?')">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.delete')): ?>
                                <form method="POST" action="<?= url('enquiries/' . $enq['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e(trim($enq['first_name'] . ' ' . ($enq['last_name'] ?? ''))) ?>"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($enquiries['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $enquiries; $baseUrl = url('enquiries') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
