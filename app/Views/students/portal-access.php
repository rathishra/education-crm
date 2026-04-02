<?php $pageTitle = 'Portal User Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-key me-2 text-success"></i>Portal User Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
                <li class="breadcrumb-item active">Portal Access</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#bulkModal">
            <i class="fas fa-users-cog me-1"></i>Bulk Actions
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($stats['total'] ?? 0) ?></div>
                        <div class="small text-muted">Total Students</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($stats['enabled'] ?? 0) ?></div>
                        <div class="small text-muted">Portal Enabled</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($stats['disabled'] ?? 0) ?></div>
                        <div class="small text-muted">Disabled</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($stats['no_password'] ?? 0) ?></div>
                        <div class="small text-muted">No Password Set</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($stats['ever_logged_in'] ?? 0) ?></div>
                        <div class="small text-muted">Ever Logged In</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('students/portal-access') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, ID, email…"
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['courseId'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="batch_id" class="form-select form-select-sm">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['batchId'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="access" class="form-select form-select-sm">
                    <option value="">All Access</option>
                    <option value="enabled"     <?= ($filters['access'] ?? '') === 'enabled'     ? 'selected' : '' ?>>Enabled</option>
                    <option value="disabled"    <?= ($filters['access'] ?? '') === 'disabled'    ? 'selected' : '' ?>>Disabled</option>
                    <option value="no_password" <?= ($filters['access'] ?? '') === 'no_password' ? 'selected' : '' ?>>No Password</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('students/portal-access') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<form method="POST" action="<?= url('students/portal-bulk') ?>" id="bulkForm">
    <?= csrfField() ?>
    <input type="hidden" name="bulk_action" id="bulkActionInput">
    <input type="hidden" name="bulk_password" id="bulkPasswordInput">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2 px-3">
        <div class="fw-semibold text-muted small">
            <input type="checkbox" id="selectAll" class="form-check-input me-2">
            <?= number_format($total) ?> student<?= $total !== 1 ? 's' : '' ?> found
        </div>
        <div class="small text-muted">Page <?= $page ?> of <?= $pages ?></div>
    </div>

    <?php if (empty($students)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-users d-block fs-1 mb-3 opacity-25"></i>
        <div class="fw-semibold">No students found</div>
        <div class="small">Try adjusting your filters.</div>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem">
            <thead class="table-light">
                <tr>
                    <th width="36"><input type="checkbox" id="selectAllHead" class="form-check-input"></th>
                    <th>Student</th>
                    <th>Course / Batch</th>
                    <th class="text-center">Portal Access</th>
                    <th class="text-center">Password</th>
                    <th>Last Login</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s):
                $enabled     = !empty($s['portal_enabled']);
                $hasPassword = !empty($s['has_password']);
                $fullName    = e(trim($s['first_name'] . ' ' . $s['last_name']));
            ?>
            <tr>
                <td><input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" class="form-check-input row-check"></td>
                <td>
                    <div class="fw-semibold"><?= $fullName ?></div>
                    <div class="text-muted" style="font-size:0.78rem"><?= e($s['student_id_number']) ?></div>
                    <?php if ($s['email']): ?>
                    <div class="text-muted" style="font-size:0.75rem"><i class="fas fa-envelope me-1 opacity-50"></i><?= e($s['email']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div><?= e($s['course_name'] ?? '—') ?></div>
                    <div class="text-muted small"><?= e($s['batch_name'] ?? '—') ?></div>
                </td>
                <td class="text-center">
                    <?php if ($enabled): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                        <i class="fas fa-check me-1"></i>Enabled
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">
                        <i class="fas fa-times me-1"></i>Disabled
                    </span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($hasPassword): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                        <i class="fas fa-lock me-1"></i>Set
                    </span>
                    <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2">
                        <i class="fas fa-unlock me-1"></i>Not Set
                    </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($s['last_portal_login_at']): ?>
                    <div style="font-size:0.8rem"><?= date('d M Y', strtotime($s['last_portal_login_at'])) ?></div>
                    <div class="text-muted" style="font-size:0.72rem"><?= date('h:i A', strtotime($s['last_portal_login_at'])) ?></div>
                    <?php else: ?>
                    <span class="text-muted small">Never</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <!-- Toggle Access -->
                        <form method="POST" action="<?= url('students/' . $s['id'] . '/portal-toggle') ?>">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-sm <?= $enabled ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                    title="<?= $enabled ? 'Disable' : 'Enable' ?> Portal Access"
                                    onclick="return confirm('<?= $enabled ? 'Disable' : 'Enable' ?> portal access for <?= $fullName ?>?')">
                                <i class="fas <?= $enabled ? 'fa-ban' : 'fa-check' ?>"></i>
                            </button>
                        </form>
                        <!-- Set Password -->
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                title="Set Password"
                                onclick="openSetPassword(<?= $s['id'] ?>, '<?= $fullName ?>')">
                            <i class="fas fa-key"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-2 px-3">
        <div class="small text-muted">Showing <?= (($page-1)*20)+1 ?>–<?= min($page*20, $total) ?> of <?= $total ?></div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</form>

<!-- ── Set Password Modal ──────────────────────────────────────── -->
<div class="modal fade" id="setPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6"><i class="fas fa-key me-2 text-primary"></i>Set Portal Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="setPasswordForm" action="">
                <?= csrfField() ?>
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="setPasswordStudent"></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Min 6 characters" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Re-enter password" required>
                    </div>
                    <div class="mt-2 p-2 rounded-2 bg-success-subtle small text-success">
                        <i class="fas fa-info-circle me-1"></i>Portal access will also be enabled automatically.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Save Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Bulk Actions Modal ─────────────────────────────────────── -->
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6"><i class="fas fa-users-cog me-2 text-success"></i>Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Select students using the checkboxes in the table, then choose an action below.</p>

                <div class="d-grid gap-2">
                    <!-- Enable all selected -->
                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="submitBulk('enable')">
                        <i class="fas fa-check-circle me-2"></i>Enable Portal Access for Selected
                    </button>
                    <!-- Disable all selected -->
                    <button type="button" class="btn btn-outline-danger btn-sm text-start" onclick="submitBulk('disable')">
                        <i class="fas fa-ban me-2"></i>Disable Portal Access for Selected
                    </button>
                </div>

                <hr>
                <div class="fw-semibold small mb-2">Set Password for Selected</div>
                <div class="mb-2">
                    <input type="password" id="bulkPwInput" class="form-control form-control-sm" placeholder="Password (min 6 chars)">
                </div>
                <button type="button" class="btn btn-primary btn-sm w-100" onclick="submitBulkPassword()">
                    <i class="fas fa-key me-2"></i>Set Password &amp; Enable Portal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
document.getElementById('selectAllHead')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    document.getElementById('selectAll').checked = this.checked;
});

function openSetPassword(id, name) {
    document.getElementById('setPasswordForm').action = '/students/' + id + '/portal-password';
    document.getElementById('setPasswordStudent').textContent = 'Setting password for: ' + name;
    new bootstrap.Modal(document.getElementById('setPasswordModal')).show();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
}

function submitBulk(action) {
    const ids = getSelectedIds();
    if (!ids.length) { alert('Please select at least one student.'); return; }
    if (!confirm('Apply "' + action + '" to ' + ids.length + ' student(s)?')) return;
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkPasswordInput').value = '';
    document.getElementById('bulkForm').submit();
}

function submitBulkPassword() {
    const ids = getSelectedIds();
    const pw  = document.getElementById('bulkPwInput').value;
    if (!ids.length) { alert('Please select at least one student.'); return; }
    if (pw.length < 6) { alert('Password must be at least 6 characters.'); return; }
    if (!confirm('Set password for ' + ids.length + ' student(s) and enable their portal access?')) return;
    document.getElementById('bulkActionInput').value  = 'set_password';
    document.getElementById('bulkPasswordInput').value = pw;
    document.getElementById('bulkForm').submit();
}
</script>
