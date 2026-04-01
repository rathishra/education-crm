<?php $pageTitle = 'Assessments & Examinations'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-clipboard-list me-2 text-primary"></i>Assessments</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Assessments</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('academic/assessments/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>New Assessment
    </a>
</div>

<!-- ── STAT CARDS ─────────────────────────────────────────── -->
<?php
$total     = count($assessments);
$active    = count(array_filter($assessments, fn($a) => $a['status'] === 'active'));
$completed = count(array_filter($assessments, fn($a) => $a['status'] === 'completed'));
$published = count(array_filter($assessments, fn($a) => $a['status'] === 'published'));
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-clipboard-list text-primary"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $total ?></div>
                    <div class="text-muted small">Total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-hourglass-half text-warning"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $active ?></div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-check-double text-success"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $completed ?></div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-lock text-info"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $published ?></div>
                    <div class="text-muted small">Published</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── TABLE ─────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="assessmentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Batch / Subject</th>
                        <th>Assessment</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Marks</th>
                        <th class="text-center">Wt%</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assessments)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-2x d-block mb-2 opacity-25"></i>
                            No assessments found. <a href="<?= url('academic/assessments/create') ?>">Create one.</a>
                        </td>
                    </tr>
                    <?php else: foreach ($assessments as $a):
                        $statusInfo = match($a['status']) {
                            'published'  => ['badge bg-info',    'fas fa-lock',       'Published'],
                            'completed'  => ['badge bg-success', 'fas fa-check-double','Completed'],
                            'active'     => ['badge bg-warning text-dark', 'fas fa-clock', 'Active'],
                            default      => ['badge bg-secondary','fas fa-circle',     ucfirst($a['status'])],
                        };
                        [$badgeCls, $icon, $statusLabel] = $statusInfo;
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark"><?= e($a['program_name']) ?></div>
                            <div class="small text-muted"><?= e($a['batch_term']) ?></div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle" style="font-size:.68rem">
                                <?= e($a['subject_code'] ?? '') ?> — <?= e($a['subject_name']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= e($a['assessment_name']) ?></div>
                            <?php if ($a['assessment_date']): ?>
                            <div class="small text-muted"><i class="far fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($a['assessment_date'])) ?></div>
                            <?php endif; ?>
                            <?php if ($a['schema_name'] ?? ''): ?>
                            <div class="small text-info"><i class="fas fa-star-half-alt me-1"></i><?= e($a['schema_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= strtoupper($a['assessment_type']) ?></span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold"><?= $a['max_marks'] ?></div>
                            <div class="small text-danger">Pass: <?= $a['passing_marks'] ?></div>
                        </td>
                        <td class="text-center"><span class="badge bg-info text-dark"><?= $a['weightage'] ?>%</span></td>
                        <td class="text-center">
                            <span class="<?= $badgeCls ?>">
                                <i class="<?= $icon ?> me-1"></i><?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('academic/assessments/marks?id='.$a['id']) ?>"
                                   class="btn btn-primary <?= $a['status'] === 'published' ? 'disabled' : '' ?>"
                                   title="Enter Marks">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="<?= url('academic/assessments/'.$a['id'].'/show') ?>"
                                   class="btn btn-outline-info" title="View Results">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <button class="btn btn-outline-<?= $a['status'] === 'published' ? 'warning' : 'success' ?> btn-publish-assessment"
                                        data-id="<?= $a['id'] ?>"
                                        data-status="<?= $a['status'] ?>"
                                        title="<?= $a['status'] === 'published' ? 'Unlock Marks' : 'Publish & Lock Marks' ?>">
                                    <i class="fas fa-<?= $a['status'] === 'published' ? 'lock-open' : 'lock' ?>"></i>
                                </button>
                                <?php if ($a['status'] !== 'published'): ?>
                                <button class="btn btn-outline-danger btn-delete-assessment"
                                        data-id="<?= $a['id'] ?>"
                                        data-name="<?= e($a['assessment_name']) ?>"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#assessmentsTable').DataTable({ order: [[0,'asc']], pageLength: 25 });
    }

    // Publish / Lock Toggle
    document.querySelectorAll('.btn-publish-assessment').forEach(btn => {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const status = this.dataset.status;
            const action = status === 'published' ? 'unlock marks?' : 'publish & lock marks? Students can then see results.';
            if (!confirm('Are you sure you want to ' + action)) return;

            fetch(`<?= url('academic/assessments') ?>/${id}/publish`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                });
        });
    });

    // Delete
    document.querySelectorAll('.btn-delete-assessment').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;
            if (!confirm(`Delete assessment "${name}" and ALL its marks? This cannot be undone.`)) return;

            fetch(`<?= url('academic/assessments') ?>/${id}/delete`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') this.closest('tr').remove();
                    else alert(res.message);
                });
        });
    });
});
</script>
