<?php $pageTitle = 'Library Issues & Returns'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-hand-holding-box me-2"></i>Issues & Returns</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li>
                <li class="breadcrumb-item active">Issues</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('library') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Inventory</a>
        <?php if (hasPermission('library.issue')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueModal"><i class="fas fa-plus me-1"></i> Issue Book</button>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link <?= $status === 'all' ? 'active' : '' ?>" href="<?= url('library/issues?status=all') ?>">All Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'issued' ? 'active' : '' ?>" href="<?= url('library/issues?status=issued') ?>">Currently Issued</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'returned' ? 'active' : '' ?>" href="<?= url('library/issues?status=returned') ?>">Returned</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'lost' ? 'active' : '' ?>" href="<?= url('library/issues?status=lost') ?>">Lost</a>
            </li>
        </ul>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Book</th>
                        <th>Borrower</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <?php if (hasPermission('library.issue')): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($issues['data'])): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($issues['data'] as $req): ?>
                        <tr class="<?= $req['status'] === 'issued' && strtotime($req['due_date']) < time() ? 'table-warning' : '' ?>">
                            <td>
                                <div class="fw-bold text-primary"><?= e($req['title']) ?></div>
                                <code class="small text-muted">ISBN: <?= e($req['isbn'] ?: 'N/A') ?></code>
                            </td>
                            <td>
                                <div class="fw-medium"><?= e($req['borrower_name']) ?></div>
                                <span class="badge bg-light text-dark border"><?= ucfirst($req['user_type']) ?></span>
                            </td>
                            <td><?= formatDate($req['issue_date']) ?></td>
                            <td>
                                <?php if ($req['status'] === 'issued' && strtotime($req['due_date']) < time()): ?>
                                    <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> <?= formatDate($req['due_date']) ?></span>
                                <?php else: ?>
                                    <?= formatDate($req['due_date']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'issued' => 'info',
                                    'returned' => 'success',
                                    'lost' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$req['status']] ?>"><?= ucfirst($req['status']) ?></span>
                                <?php if ($req['status'] === 'returned' || $req['status'] === 'lost'): ?>
                                    <div class="small text-muted mt-1">on <?= formatDate($req['return_date']) ?></div>
                                    <?php if ($req['fine_amount'] > 0): ?>
                                        <div class="small text-danger fw-bold">Fine: <?= formatCurrency($req['fine_amount']) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php if (hasPermission('library.issue')): ?>
                            <td class="text-end">
                                <?php if ($req['status'] === 'issued'): ?>
                                    <button class="btn btn-sm btn-outline-success" onclick="openReturnModal(<?= $req['id'] ?>, '<?= e(addslashes($req['title'])) ?>', '<?= e(addslashes($req['borrower_name'])) ?>')">
                                        <i class="fas fa-undo me-1"></i> Process Return
                                    </button>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($issues['last_page'] > 1): ?>
    <div class="card-footer">
        <?= renderPagination($issues) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('library/issues') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Issue Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Select Book</label>
                    <select class="form-select" name="book_id" required>
                        <option value="">-- Select Available Book --</option>
                        <?php foreach ($books as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= e($b['title']) ?> (<?= $b['available_copies'] ?> avail)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Student ID Lookup</label>
                    <input type="number" class="form-control" name="student_id" required placeholder="Enter strictly the Student DB ID for now">
                    <small class="text-muted">In a full UI, this would be an AJAX searchable dropdown finding active students.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Due Date</label>
                    <input type="date" class="form-control" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                    <small class="text-muted">Default is 14 days from today.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Issue Book</button>
            </div>
        </form>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" id="returnForm" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Process Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Book:</strong> <span id="returnBookName"></span></p>
                <p><strong>Borrower:</strong> <span id="returnBorrowerName"></span></p>
                
                <div class="mb-3 mt-4">
                    <label class="form-label required">Return Status</label>
                    <select class="form-select" name="status" required>
                        <option value="returned">Returned in Good Condition</option>
                        <option value="lost">Lost / Damaged</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fine Amount (if any)</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= config('app.currency', '$') ?></span>
                        <input type="number" step="0.01" min="0" class="form-control" name="fine_amount" value="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Complete Return</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(issueId, bookName, borrowerName) {
    document.getElementById('returnBookName').textContent = bookName;
    document.getElementById('returnBorrowerName').textContent = borrowerName;
    document.getElementById('returnForm').action = '<?= url('library/issues/') ?>' + issueId + '/return';
    
    var modal = new bootstrap.Modal(document.getElementById('returnModal'));
    modal.show();
}
</script>
