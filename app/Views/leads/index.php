<?php $pageTitle = 'Lead Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-plus me-2"></i>Lead Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Leads</li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('leads.export')): ?>
        <a href="<?= url('leads/export') . '?' . http_build_query(array_filter($filters ?? [])) ?>" class="btn btn-outline-success me-1">
            <i class="fas fa-file-csv me-1"></i>Export
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.import')): ?>
        <a href="<?= url('leads/import') ?>" class="btn btn-outline-info me-1">
            <i class="fas fa-file-import me-1"></i>Import
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.create')): ?>
        <a href="<?= url('leads/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Lead
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Pipeline Quick View -->
<div class="row g-2 mb-4">
    <?php
    $statusCounts = [];
    foreach ($statuses as $st) {
        $statusCounts[$st['id']] = 0;
    }
    ?>
    <?php foreach ($statuses as $st): ?>
    <div class="col">
        <a href="<?= url('leads?status_id=' . $st['id']) ?>" class="text-decoration-none">
            <div class="pipeline-stage" style="background-color:<?= e($st['color']) ?>">
                <div class="small"><?= e($st['name']) ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, phone, email..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status_id">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st['id'] ?>" <?= ($filters['status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                        <?= e($st['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="source_id">
                    <option value="">All Sources</option>
                    <?php foreach ($sources as $src): ?>
                    <option value="<?= $src['id'] ?>" <?= ($filters['source_id'] ?? '') == $src['id'] ? 'selected' : '' ?>>
                        <?= e($src['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="assigned_to">
                    <option value="">All Counselors</option>
                    <?php foreach ($counselors as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">Priority</option>
                    <?php foreach (['low','medium','high','urgent'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($filters['date_from'] ?? '') ?>" title="From date">
            </div>
            <div class="col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($filters['date_to'] ?? '') ?>" title="To date">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($leads['total'] ?? 0) ?></strong> leads</span>
        <a href="<?= url('leads') ?>" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Lead #</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Course</th>
                        <th>Source</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads['data'])): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No leads found</td></tr>
                    <?php else: ?>
                    <?php foreach ($leads['data'] as $lead): ?>
                    <tr>
                        <td><a href="<?= url('leads/' . $lead['id']) ?>" class="text-decoration-none"><code><?= e($lead['lead_number']) ?></code></a></td>
                        <td>
                            <a href="<?= url('leads/' . $lead['id']) ?>" class="fw-semibold">
                                <?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?>
                            </a>
                            <?php if ($lead['is_duplicate']): ?>
                            <span class="badge bg-warning text-dark" title="Possible duplicate"><i class="fas fa-copy"></i></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= e($lead['phone']) ?></div>
                            <small class="text-muted"><?= e($lead['email'] ?? '') ?></small>
                        </td>
                        <td><small><?= e($lead['course_name'] ?? '-') ?></small></td>
                        <td><small><?= e($lead['source_name'] ?? '-') ?></small></td>
                        <td><small><?= e($lead['assigned_name'] ?? '<span class="text-danger">Unassigned</span>') ?></small></td>
                        <td>
                            <span class="priority-<?= $lead['priority'] ?>"><?= ucfirst($lead['priority']) ?></span>
                        </td>
                        <td>
                            <span class="badge" style="background-color:<?= e($lead['status_color'] ?? '#6c757d') ?>">
                                <?= e($lead['status_name'] ?? '-') ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= formatDate($lead['created_at']) ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('leads/' . $lead['id']) ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('leads.edit')): ?>
                                <a href="<?= url('leads/' . $lead['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('leads.delete')): ?>
                                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($lead['first_name']) ?>"><i class="fas fa-trash"></i></button>
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
    <?php if (($leads['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $leads; $baseUrl = url('leads') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
