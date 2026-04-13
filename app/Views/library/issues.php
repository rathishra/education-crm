<?php $pageTitle = 'Library — Issues & Returns'; ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exchange-alt me-2 text-primary"></i>Issues &amp; Returns</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li>
                <li class="breadcrumb-item active">Issues &amp; Returns</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('library') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Inventory
        </a>
        <?php if (hasPermission('library.manage')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueModal">
            <i class="fas fa-plus me-1"></i>Issue Book
        </button>
        <?php endif; ?>
    </div>
</div>

<?php $flash = getFlash('success'); $flashErr = getFlash('error'); ?>
<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= e($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashErr): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($flashErr) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter Pills -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'all' || $filter === '' ? 'active' : '' ?>"
                   href="<?= url('library/issues?filter=all') ?>">All Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'issued' ? 'active' : '' ?>"
                   href="<?= url('library/issues?filter=issued') ?>">Currently Issued</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'overdue' ? 'active' : '' ?>"
                   href="<?= url('library/issues?filter=overdue') ?>">
                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>Overdue
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'returned' ? 'active' : '' ?>"
                   href="<?= url('library/issues?filter=returned') ?>">Returned</a>
            </li>
        </ul>
    </div>
</div>

<!-- Issues Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fas fa-list me-2"></i>Issue Records</span>
        <span class="badge bg-secondary"><?= count($issues) ?> record<?= count($issues) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Book</th>
                        <th>Student</th>
                        <th>Issued</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                        <th class="text-center">Status</th>
                        <?php if (hasPermission('library.manage')): ?>
                        <th class="text-end">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($issues)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                            No records found for this filter.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($issues as $iss): ?>
                    <?php
                        $isOverdue = $iss['status'] === 'issued' && strtotime($iss['due_date']) < time();
                        $daysOver  = max(0, (int)($iss['days_overdue'] ?? 0));
                    ?>
                    <tr class="<?= $isOverdue ? 'table-warning' : '' ?>">
                        <td>
                            <div class="fw-semibold"><?= e($iss['book_title']) ?></div>
                            <code class="small text-muted"><?= e($iss['accession_number']) ?></code>
                        </td>
                        <td>
                            <div class="fw-medium"><?= e($iss['first_name'] . ' ' . $iss['last_name']) ?></div>
                            <code class="small text-muted"><?= e($iss['student_id_number']) ?></code>
                        </td>
                        <td class="small"><?= date('d M Y', strtotime($iss['issued_date'])) ?></td>
                        <td class="small">
                            <?php if ($isOverdue): ?>
                            <span class="text-danger fw-semibold">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?= date('d M Y', strtotime($iss['due_date'])) ?>
                            </span>
                            <div class="small text-danger"><?= $daysOver ?> day<?= $daysOver !== 1 ? 's' : '' ?> overdue</div>
                            <?php else: ?>
                            <?= $iss['due_date'] ? date('d M Y', strtotime($iss['due_date'])) : '—' ?>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?= $iss['return_date'] ? date('d M Y', strtotime($iss['return_date'])) : '—' ?>
                        </td>
                        <td class="small">
                            <?php if ((float)$iss['fine_amount'] > 0): ?>
                            <span class="text-danger fw-semibold">₹<?= number_format($iss['fine_amount'], 2) ?></span>
                            <?php elseif ($isOverdue): ?>
                            <span class="text-warning small">₹<?= number_format($daysOver * (float)$iss['fine_per_day'], 2) ?> est.</span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $badgeMap = ['issued' => 'info', 'returned' => 'success', 'overdue' => 'warning', 'lost' => 'danger'];
                            $displayStatus = $isOverdue ? 'overdue' : $iss['status'];
                            $badgeColor = $badgeMap[$displayStatus] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeColor ?>">
                                <?= ucfirst($displayStatus) ?>
                            </span>
                        </td>
                        <?php if (hasPermission('library.manage')): ?>
                        <td class="text-end">
                            <?php if ($iss['status'] === 'issued'): ?>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    onclick="openReturnModal(
                                        <?= (int)$iss['id'] ?>,
                                        '<?= e(addslashes($iss['book_title'])) ?>',
                                        '<?= e(addslashes($iss['first_name'] . ' ' . $iss['last_name'])) ?>',
                                        <?= $daysOver ?>,
                                        <?= (float)$iss['fine_per_day'] ?>
                                    )">
                                <i class="fas fa-undo me-1"></i>Return
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
</div>

<!-- ── Issue Book Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('library/issues') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book me-2"></i>Issue Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Book <span class="text-danger">*</span></label>
                    <select class="form-select" name="book_id" required>
                        <option value="">— Select Available Book —</option>
                        <?php foreach ($availableBooks as $ab): ?>
                        <option value="<?= $ab['id'] ?>">
                            <?= e($ab['title']) ?> — <?= e($ab['author']) ?>
                            (<?= $ab['available_quantity'] ?> avail · <?= e($ab['accession_number']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($availableBooks)): ?>
                    <div class="form-text text-danger">No books available for issue at this time.</div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Student ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="student_id" required
                           placeholder="Enter student database ID">
                    <div class="form-text">Enter the numeric ID of the student from the Students module.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="due_date"
                               value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Fine Per Day (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="fine_per_day"
                                   value="1.00" min="0" step="0.50">
                        </div>
                        <div class="form-text">Charged per overdue day on return.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" <?= empty($availableBooks) ? 'disabled' : '' ?>>
                    <i class="fas fa-check me-1"></i>Issue Book
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Return Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" id="returnForm" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Process Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3 py-2 small">
                    <strong>Book:</strong> <span id="returnBookName"></span><br>
                    <strong>Student:</strong> <span id="returnStudentName"></span>
                </div>
                <div id="fineAlert" class="alert alert-warning d-none py-2 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This book is <strong id="returnDaysOver"></strong> day(s) overdue.
                    Estimated fine: <strong id="returnFineEst"></strong>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-medium">Fine Amount Collected (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" name="fine_amount_manual"
                               id="returnFineInput" value="0.00" min="0" step="0.50">
                    </div>
                    <div class="form-text">Leave 0 if no fine is being collected. Fine is auto-calculated on save.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle me-1"></i>Confirm Return
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(issueId, bookName, studentName, daysOverdue, finePerDay) {
    document.getElementById('returnBookName').textContent    = bookName;
    document.getElementById('returnStudentName').textContent = studentName;
    document.getElementById('returnForm').action = '<?= url('library/issues/') ?>' + issueId + '/return';

    var fineAlert = document.getElementById('fineAlert');
    var fineInput = document.getElementById('returnFineInput');

    if (daysOverdue > 0) {
        var est = (daysOverdue * finePerDay).toFixed(2);
        document.getElementById('returnDaysOver').textContent = daysOverdue;
        document.getElementById('returnFineEst').textContent  = '₹' + est;
        fineInput.value = est;
        fineAlert.classList.remove('d-none');
    } else {
        fineAlert.classList.add('d-none');
        fineInput.value = '0.00';
    }

    new bootstrap.Modal(document.getElementById('returnModal')).show();
}
</script>
