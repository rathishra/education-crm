<?php $pageTitle = 'Audit Logs'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-shield-alt me-2"></i>Audit Logs</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li>
                <li class="breadcrumb-item active">Audit Logs</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings') ?>"><i class="fas fa-sliders-h me-1"></i>General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('settings/communication') ?>"><i class="fas fa-paper-plane me-1"></i>Communication</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="<?= url('settings/audit') ?>"><i class="fas fa-shield-alt me-1"></i>Audit Logs</a>
    </li>
</ul>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search by user, entity, description..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="action">
                    <option value="">All Actions</option>
                    <?php foreach ($actions ?? [] as $act): ?>
                    <option value="<?= e($act) ?>" <?= ($action ?? '') === $act ? 'selected' : '' ?>>
                        <?= ucfirst(e($act)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($dateFrom ?? '') ?>" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($dateTo ?? '') ?>" placeholder="To">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('settings/audit') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity Type</th>
                        <th>Entity ID</th>
                        <th>IP Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs['data'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No audit logs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs['data'] as $log): ?>
                    <?php
                    $actionColors = [
                        'create'  => 'success', 'store'   => 'success',
                        'update'  => 'primary',  'edit'    => 'primary',
                        'delete'  => 'danger',   'destroy' => 'danger',
                        'login'   => 'info',     'logout'  => 'secondary',
                        'export'  => 'warning',
                    ];
                    $actionColor = $actionColors[strtolower($log['action'] ?? '')] ?? 'secondary';
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($log['user_name'] ?? '—') ?></strong>
                            <small class="text-muted d-block"><?= e($log['user_email'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?= $actionColor ?>"><?= ucfirst(e($log['action'] ?? '—')) ?></span>
                        </td>
                        <td><?= e($log['entity_type'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($log['entity_id'])): ?>
                            <code>#<?= e($log['entity_id']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code class="text-muted small"><?= e($log['ip_address'] ?? '—') ?></code>
                        </td>
                        <td>
                            <span title="<?= e($log['created_at'] ?? '') ?>">
                                <?= !empty($log['created_at']) ? timeAgo($log['created_at']) : '—' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($logs['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $logs; $baseUrl = url('settings/audit') . '?' . http_build_query(array_filter(compact('search', 'action', 'userId', 'dateFrom', 'dateTo') ?? [])); include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
