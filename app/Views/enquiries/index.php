<?php $pageTitle = 'Enquiry Management'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-question-circle me-2 text-primary"></i>Enquiry Management</h1>
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

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-list-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=new') ?>" class="text-decoration-none">
            <div class="stat-card stat-sky py-3">
                <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['new_count'] ?? 0) ?></div>
                    <div class="stat-label">New</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=interested') ?>" class="text-decoration-none">
            <div class="stat-card stat-emerald py-3">
                <div class="stat-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['interested'] ?? 0) ?></div>
                    <div class="stat-label">Interested</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?priority=hot') ?>" class="text-decoration-none">
            <div class="stat-card stat-rose py-3">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['hot'] ?? 0) ?></div>
                    <div class="stat-label">Hot</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=converted') ?>" class="text-decoration-none">
            <div class="stat-card stat-violet py-3">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['converted'] ?? 0) ?></div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['this_month'] ?? 0) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Name, phone, email, enquiry#..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['new','contacted','interested','not_interested','converted','closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                        <?= ucwords(str_replace('_', ' ', $s)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">All Priority</option>
                    <option value="hot"  <?= ($filters['priority'] ?? '') === 'hot'  ? 'selected' : '' ?>>Hot</option>
                    <option value="warm" <?= ($filters['priority'] ?? '') === 'warm' ? 'selected' : '' ?>>Warm</option>
                    <option value="cold" <?= ($filters['priority'] ?? '') === 'cold' ? 'selected' : '' ?>>Cold</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="source">
                    <option value="">All Sources</option>
                    <?php foreach ($sources as $src): ?>
                    <option value="<?= e($src) ?>" <?= ($filters['source'] ?? '') === $src ? 'selected' : '' ?>>
                        <?= e($src) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="counselor_id">
                    <option value="">All Counselors</option>
                    <?php foreach ($counselors as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['counselor_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($filters['date_from'] ?? '') ?>" title="From">
            </div>
            <div class="col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($filters['date_to'] ?? '') ?>" title="To">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <a href="<?= url('enquiries') ?>" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            Total: <strong><?= number_format($enquiries['total'] ?? 0) ?></strong> enquiries
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($enquiries['data'])): ?>
        <div class="text-center py-5">
            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-2">No enquiries found.</p>
            <?php if (hasPermission('enquiries.create')): ?>
            <a href="<?= url('enquiries/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add First Enquiry
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Enquiry #</th>
                        <th>Name &amp; Phone</th>
                        <th>Course</th>
                        <th>Source</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Counselor</th>
                        <th>Date</th>
                        <th width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enquiries['data'] as $enq):
                        $fullName = trim($enq['first_name'] . ' ' . ($enq['last_name'] ?? ''));

                        $priorityClasses = [
                            'hot'  => 'badge bg-soft-danger text-danger',
                            'warm' => 'badge bg-soft-warning text-warning',
                            'cold' => 'badge bg-soft-info text-info',
                        ];
                        $priorityClass = $priorityClasses[$enq['priority'] ?? 'warm'] ?? 'badge bg-secondary';

                        $statusClasses = [
                            'new'          => 'badge bg-soft-primary text-primary',
                            'contacted'    => 'badge bg-soft-info text-info',
                            'interested'   => 'badge bg-soft-success text-success',
                            'not_interested'=> 'badge bg-secondary',
                            'converted'    => 'badge bg-soft-primary',
                            'closed'       => 'badge text-muted bg-light border',
                        ];
                        $statusClass = $statusClasses[$enq['status'] ?? 'new'] ?? 'badge bg-secondary';
                        $statusLabel = ucwords(str_replace('_', ' ', $enq['status'] ?? 'new'));
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url('enquiries/' . $enq['id']) ?>" class="text-decoration-none">
                                <code class="fs-xs"><?= e($enq['enquiry_number']) ?></code>
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                <a href="<?= url('enquiries/' . $enq['id']) ?>" class="text-dark text-decoration-none">
                                    <?= e($fullName) ?>
                                </a>
                            </div>
                            <small class="text-muted">
                                <a href="tel:<?= e($enq['phone']) ?>" class="text-muted text-decoration-none">
                                    <?= e($enq['phone']) ?>
                                </a>
                            </small>
                        </td>
                        <td>
                            <small><?= e($enq['course_name'] ?? '-') ?></small>
                            <?php if (!empty($enq['department_name'])): ?>
                            <br><small class="text-muted"><?= e($enq['department_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= e($enq['source'] ?? '-') ?></small></td>
                        <td>
                            <span class="<?= $priorityClass ?>">
                                <?php if (($enq['priority'] ?? '') === 'hot'): ?>
                                    <i class="fas fa-fire me-1"></i>
                                <?php elseif (($enq['priority'] ?? '') === 'cold'): ?>
                                    <i class="fas fa-snowflake me-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-thermometer-half me-1"></i>
                                <?php endif; ?>
                                <?= ucfirst($enq['priority'] ?? 'warm') ?>
                            </span>
                        </td>
                        <td><span class="<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                        <td><small><?= e($enq['counselor_name'] ?? $enq['assigned_to_name'] ?? '-') ?></small></td>
                        <td><small class="text-muted"><?= formatDate($enq['created_at']) ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('enquiries/' . $enq['id']) ?>"
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('enquiries.edit') && $enq['status'] !== 'converted'): ?>
                                <a href="<?= url('enquiries/' . $enq['id'] . '/edit') ?>"
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.convert') && $enq['status'] !== 'converted'): ?>
                                <form method="POST"
                                      action="<?= url('enquiries/' . $enq['id'] . '/convert') ?>"
                                      class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-success" title="Convert to Lead"
                                            onclick="return confirm('Convert this enquiry to a lead?')">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.delete')): ?>
                                <form method="POST"
                                      action="<?= url('enquiries/' . $enq['id'] . '/delete') ?>"
                                      class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-delete"
                                            data-name="<?= e($fullName) ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if (($enquiries['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $enquiries;
        $baseUrl    = url('enquiries') . '?' . http_build_query(array_filter($filters ?? []));
        ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
