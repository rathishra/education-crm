<?php $pageTitle = 'Communication Log'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-history me-2"></i>Communication Log</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Communication Log</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('communication/bulk') ?>" class="btn btn-primary">
        <i class="fas fa-bullhorn me-1"></i>Bulk Send
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search recipient, message..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <option value="sms" <?= ($type ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
                    <option value="email" <?= ($type ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="whatsapp" <?= ($type ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('communication/log') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th class="text-center">Status</th>
                        <th>Sent By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($communications['data'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No communication logs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($communications['data'] as $log): ?>
                    <?php
                    $typeBadges = ['sms' => 'bg-info', 'email' => 'bg-primary', 'whatsapp' => 'bg-success'];
                    $statusColors = ['sent' => 'success', 'failed' => 'danger', 'pending' => 'warning', 'delivered' => 'info'];
                    $badgeClass = $typeBadges[$log['type']] ?? 'bg-secondary';
                    $statusColor = $statusColors[$log['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($log['recipient_name'] ?? '—') ?></strong>
                            <small class="text-muted d-block"><?= e($log['recipient_contact'] ?? '') ?></small>
                        </td>
                        <td><span class="badge <?= $badgeClass ?>"><?= strtoupper(e($log['type'])) ?></span></td>
                        <td>
                            <span title="<?= e($log['message'] ?? '') ?>">
                                <?= e(mb_substr($log['message'] ?? '—', 0, 80)) ?><?= strlen($log['message'] ?? '') > 80 ? '…' : '' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(e($log['status'] ?? 'pending')) ?></span>
                        </td>
                        <td><?= e($log['sent_by_name'] ?? '—') ?></td>
                        <td><?= !empty($log['created_at']) ? timeAgo($log['created_at']) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($communications['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $communications;
        $baseUrl = url('communication/log') . '?' . http_build_query(array_filter(compact('search', 'type') ?? []));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
