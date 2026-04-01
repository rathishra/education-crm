<?php $pageTitle = 'Subject Allocation'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-book-reader me-2 text-primary"></i>Subject Allocation</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Subject Allocation</li>
            </ol>
        </nav>
    </div>
    <?php if ($batchId): ?>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#copyModal">
            <i class="fas fa-copy me-1"></i>Copy from Batch
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="fas fa-plus me-1"></i>Assign Subject
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ── BATCH SELECTOR ─────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold small mb-1">Select Batch</label>
                <select name="batch_id" class="form-select select2" onchange="this.form.submit()">
                    <option value="">— Choose a batch to view/manage subjects —</option>
                    <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>>
                        <?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($batchId && !empty($semesters)): ?>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Filter Semester</label>
                <select name="semester" class="form-select" onchange="this.form.submit()">
                    <option value="">All Semesters</option>
                    <?php foreach ($semesters as $sem): ?>
                    <option value="<?= $sem ?>" <?= $semester == $sem ? 'selected' : '' ?>>Semester <?= $sem ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!$batchId): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-book-reader fa-3x d-block mb-3 opacity-25"></i>
        <p>Select a batch to view and manage subject allocations.</p>
    </div>
</div>
<?php else: ?>

<!-- ── BATCH HEADER ───────────────────────────────────────── -->
<?php if ($currentBatch): ?>
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
    <div class="card-body d-flex align-items-center gap-4">
        <div>
            <div class="fw-bold fs-5"><?= e($currentBatch['program_name']) ?></div>
            <div class="text-muted"><?= e($currentBatch['batch_term']) ?></div>
        </div>
        <div class="ms-auto d-flex gap-4 text-center">
            <div>
                <div class="fw-bold fs-4 text-primary"><?= count($allocated) ?></div>
                <div class="text-muted small">Subjects Assigned</div>
            </div>
            <div>
                <div class="fw-bold fs-4 text-success"><?= count(array_filter($allocated, fn($r) => $r['faculty_count'] > 0)) ?></div>
                <div class="text-muted small">Have Faculty</div>
            </div>
            <div>
                <div class="fw-bold fs-4 text-danger"><?= count(array_filter($allocated, fn($r) => $r['faculty_count'] == 0)) ?></div>
                <div class="text-muted small">No Faculty</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── ALLOCATED SUBJECTS TABLE ──────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fas fa-check-circle me-2 text-success"></i>Assigned Subjects</span>
        <span class="badge bg-success"><?= count($allocated) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="allocatedTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Subject</th>
                        <th class="text-center">Semester</th>
                        <th class="text-center">Credits</th>
                        <th class="text-center">Max / Pass</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Faculty</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allocated)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No subjects assigned yet. <a href="#" data-bs-toggle="modal" data-bs-target="#assignModal">Assign subjects.</a>
                        </td>
                    </tr>
                    <?php else: foreach ($allocated as $a): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?= e($a['subject_name']) ?></div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle" style="font-size:.68rem">
                                <?= e($a['subject_code']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?= $a['semester'] ? 'Sem ' . $a['semester'] : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark"><?= $a['eff_credits'] ?? $a['credits'] ?? '—' ?></span>
                        </td>
                        <td class="text-center">
                            <span class="text-dark"><?= $a['max_marks'] ?></span>
                            <span class="text-muted">/</span>
                            <span class="text-danger"><?= $a['passing_marks'] ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($a['is_compulsory']): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Compulsory</span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Elective</span>
                            <?php endif; ?>
                            <?php if ($a['is_elective'] ?? 0): ?>
                                <span class="badge bg-light text-secondary border ms-1" style="font-size:.65rem">Elective Subj</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($a['faculty_count'] > 0): ?>
                                <span class="badge bg-success"><i class="fas fa-user-check me-1"></i><?= $a['faculty_count'] ?> assigned</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="fas fa-exclamation-triangle me-1"></i>No Faculty
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-danger btn-remove-allocation"
                                    data-id="<?= $a['id'] ?>"
                                    data-name="<?= e($a['subject_name']) ?>"
                                    title="Remove from batch">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── AVAILABLE SUBJECTS ─────────────────────────────────── -->
<?php if (!empty($available)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fas fa-plus-circle me-2 text-warning"></i>Available to Assign</span>
        <span class="badge bg-warning text-dark"><?= count($available) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Subject</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Credits</th>
                        <th class="text-center">Default Sem</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($available as $s): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?= e($s['subject_name']) ?></div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:.68rem"><?= e($s['subject_code']) ?></span>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary"><?= strtoupper($s['subject_type']) ?></span></td>
                        <td class="text-center"><?= $s['credits'] ?? '—' ?></td>
                        <td class="text-center"><?= $s['semester'] ? 'Sem '.$s['semester'] : '—' ?></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-success btn-quick-assign"
                                    data-id="<?= $s['id'] ?>"
                                    data-name="<?= e($s['subject_name']) ?>"
                                    data-credits="<?= $s['credits'] ?>"
                                    data-semester="<?= $s['semester'] ?>">
                                <i class="fas fa-plus me-1"></i>Assign
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── ASSIGN MODAL ───────────────────────────────────────── -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmAssign" novalidate>
                <input type="hidden" name="batch_id" value="<?= $batchId ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Assign Subject to Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="assignAlerts"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select select2Modal" id="assignSubjectId" required>
                                <option value="">— Select Subject —</option>
                                <?php foreach ($available as $s): ?>
                                <option value="<?= $s['id'] ?>"
                                        data-credits="<?= $s['credits'] ?>"
                                        data-semester="<?= $s['semester'] ?>">
                                    <?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Semester</label>
                            <input type="number" name="semester" id="assignSemester" class="form-control" min="1" max="12" placeholder="e.g. 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Credits</label>
                            <input type="number" name="credits" id="assignCredits" class="form-control" step="0.5" min="0" placeholder="e.g. 4">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Max Marks</label>
                            <input type="number" name="max_marks" class="form-control" value="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Passing Marks</label>
                            <input type="number" name="passing_marks" class="form-control" value="40">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_compulsory" id="assignCompulsory" value="1" checked>
                                <label class="form-check-label" for="assignCompulsory">Compulsory</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnAssign"><i class="fas fa-plus me-1"></i>Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── COPY FROM BATCH MODAL ─────────────────────────────── -->
<div class="modal fade" id="copyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmCopy">
                <input type="hidden" name="target_batch_id" value="<?= $batchId ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-copy me-2 text-info"></i>Copy Subjects from Another Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="copyAlerts"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Source Batch</label>
                        <select name="source_batch_id" class="form-select select2Modal" required>
                            <option value="">— Select source batch —</option>
                            <?php foreach ($batches as $b): ?>
                            <?php if ($b['id'] == $batchId) continue; ?>
                            <option value="<?= $b['id'] ?>"><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Subjects already assigned to the current batch will be skipped.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white"><i class="fas fa-copy me-1"></i>Copy Subjects</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; // end if $batchId ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // DataTable for allocated
    if (typeof $.fn.DataTable !== 'undefined' && document.getElementById('allocatedTable')) {
        $('#allocatedTable').DataTable({ paging: false, searching: true, info: false, order: [[1,'asc']] });
    }

    // Init Select2 inside modals
    document.getElementById('assignModal')?.addEventListener('shown.bs.modal', function () {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2Modal').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#assignModal') });
        }
    });
    document.getElementById('copyModal')?.addEventListener('shown.bs.modal', function () {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2Modal').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#copyModal') });
        }
    });

    // Auto-fill credits/semester when subject selected in assign modal
    document.getElementById('assignSubjectId')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('assignCredits').value  = opt.dataset.credits || '';
        document.getElementById('assignSemester').value = opt.dataset.semester || '';
    });

    // Quick assign buttons (from available table)
    document.querySelectorAll('.btn-quick-assign').forEach(btn => {
        btn.addEventListener('click', function () {
            const id       = this.dataset.id;
            const credits  = this.dataset.credits;
            const semester = this.dataset.semester;
            const sel      = document.getElementById('assignSubjectId');

            // Select this option
            if (sel) {
                sel.value = id;
                document.getElementById('assignCredits').value  = credits || '';
                document.getElementById('assignSemester').value = semester || '';
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('assignModal')).show();
        });
    });

    // Remove allocation
    document.querySelectorAll('.btn-remove-allocation').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;
            if (!confirm(`Remove "${name}" from this batch?`)) return;

            fetch(`<?= url('academic/subject-allocation') ?>/${id}/remove`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') this.closest('tr').remove();
                    else alert(res.message);
                });
        });
    });

    // Assign form submit
    document.getElementById('frmAssign')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const btn = document.getElementById('btnAssign');
        btn.disabled = true;

        fetch('<?= url('academic/subject-allocation/assign') ?>', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                if (res.status === 'success') location.reload();
                else document.getElementById('assignAlerts').innerHTML =
                    `<div class="alert alert-danger py-2">${res.message}</div>`;
            })
            .catch(() => { btn.disabled = false; });
    });

    // Copy form submit
    document.getElementById('frmCopy')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch('<?= url('academic/subject-allocation/bulk-copy') ?>', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') { alert(res.message); location.reload(); }
                else document.getElementById('copyAlerts').innerHTML =
                    `<div class="alert alert-danger py-2">${res.message}</div>`;
            });
    });
});
</script>
