<?php $pageTitle = 'Transport Allocations'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-id-card-alt me-2"></i>Transport Allocations</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('transport') ?>">Transport</a></li>
                <li class="breadcrumb-item active">Allocations</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('transport.allocate')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i> New Allocation</button>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Route</th>
                        <th>Stop</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allocations)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No transport allocations found for this academic year.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allocations as $a): ?>
                        <tr>
                            <td><?= formatDate($a['created_at']) ?></td>
                            <td>
                                <div class="fw-semibold">
                                    <a href="<?= url('students/'.$a['student_id']) ?>"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></a>
                                </div>
                                <code class="small text-muted"><?= e($a['student_id_number']) ?></code>
                            </td>
                            <td><?= e($a['route_name']) ?></td>
                            <td class="fw-bold"><?= e($a['stop_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('transport/allocations') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Allocate Transport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Student ID Lookup</label>
                    <input type="number" class="form-control" name="student_id" placeholder="Enter strictly the Student DB ID for now" required>
                    <small class="text-muted">In a full UI, this would be an AJAX searchable dropdown finding active students.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Route & Stop</label>
                    <select class="form-select" name="stop_id" required>
                        <option value="">-- Select Stop --</option>
                        <?php foreach ($routes as $route): ?>
                            <optgroup label="<?= e($route['name']) ?>">
                                <?php 
                                $stops = db()->query("SELECT id, name FROM transport_stops WHERE route_id = ? ORDER BY sort_order", [$route['id']])->fetchAll();
                                foreach($stops as $s):
                                ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Allocate</button>
            </div>
        </form>
    </div>
</div>
