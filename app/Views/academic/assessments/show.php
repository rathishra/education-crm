<?php $pageTitle = 'Assessment Results'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-chart-bar me-2 text-primary"></i><?= e($assessment['assessment_name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('academic/assessments') ?>">Assessments</a></li>
                <li class="breadcrumb-item active">Results</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/assessments/marks?id='.$assessment['id']) ?>"
           class="btn btn-outline-primary btn-sm <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>">
            <i class="fas fa-pen me-1"></i>Edit Marks
        </a>
        <a href="<?= url('academic/assessments') ?>" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- ── INFO CARD ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Batch</div>
                        <div class="fw-semibold"><?= e($assessment['program_name']) ?> (<?= e($assessment['batch_term']) ?>)</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Subject</div>
                        <div class="fw-semibold"><?= e($assessment['subject_code'] ?? '') ?> — <?= e($assessment['subject_name']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Max Marks</div>
                        <div class="fw-bold fs-5 text-dark"><?= $assessment['max_marks'] ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Passing Marks</div>
                        <div class="fw-bold fs-5 text-danger"><?= $assessment['passing_marks'] ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Weightage</div>
                        <div class="fw-bold fs-5 text-info"><?= $assessment['weightage'] ?>%</div>
                    </div>
                    <?php if ($assessment['assessment_date']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Date</div>
                        <div class="fw-semibold"><?= date('d M Y', strtotime($assessment['assessment_date'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Status</div>
                        <?php
                        $badgeCls = match($assessment['status']) {
                            'published' => 'badge bg-info',
                            'completed' => 'badge bg-success',
                            default     => 'badge bg-warning text-dark',
                        };
                        ?>
                        <span class="<?= $badgeCls ?>"><?= ucfirst($assessment['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="fs-2 fw-bold text-primary"><?= $summary['total_students'] ?></div>
                        <div class="text-muted small">Students</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-2 fw-bold text-danger"><?= $summary['absent'] ?></div>
                        <div class="text-muted small">Absent</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-2 fw-bold text-success"><?= $summary['pass'] ?></div>
                        <div class="text-muted small">Passed</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-2 fw-bold text-danger"><?= $summary['fail'] ?></div>
                        <div class="text-muted small">Failed</div>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="fw-bold text-primary"><?= $summary['avg'] ?></div>
                                <div style="font-size:.65rem" class="text-muted">AVG</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success"><?= $summary['highest'] ?></div>
                                <div style="font-size:.65rem" class="text-muted">HIGH</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-danger"><?= $summary['lowest'] ?></div>
                                <div style="font-size:.65rem" class="text-muted">LOW</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── MARKS TABLE ────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Student Results</span>
        <span class="text-muted small"><?= count($marks) ?> records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="resultsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Roll No</th>
                        <th>Student Name</th>
                        <th class="text-center">Marks</th>
                        <?php if ($assessment['evaluation_mode'] === 'internal_external'): ?>
                        <th class="text-center">Internal</th>
                        <th class="text-center">External</th>
                        <?php endif; ?>
                        <th class="text-center">%</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Result</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($marks)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No marks entered yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($marks as $m): ?>
                    <tr class="<?= $m['is_absent'] ? 'table-secondary' : ($m['is_pass'] ? '' : 'table-danger bg-opacity-10') ?>">
                        <td class="ps-4 text-muted small"><?= e($m['roll_number'] ?? '—') ?></td>
                        <td class="fw-semibold"><?= e($m['student_name']) ?></td>
                        <td class="text-center">
                            <?php if ($m['is_absent']): ?>
                                <span class="badge bg-secondary">Absent</span>
                            <?php else: ?>
                                <span class="fw-bold"><?= $m['marks_obtained'] ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($assessment['evaluation_mode'] === 'internal_external'): ?>
                        <td class="text-center"><?= $m['is_absent'] ? '—' : $m['internal_marks'] ?></td>
                        <td class="text-center"><?= $m['is_absent'] ? '—' : $m['external_marks'] ?></td>
                        <?php endif; ?>
                        <td class="text-center"><?= $m['is_absent'] ? '—' : $m['consolidated_percentage'].'%' ?></td>
                        <td class="text-center">
                            <?php if (!$m['is_absent'] && $m['grade_label']): ?>
                                <span class="badge bg-primary"><?= e($m['grade_label']) ?></span>
                                <?php if ($m['grade_point']): ?>
                                <div style="font-size:.65rem" class="text-muted"><?= $m['grade_point'] ?> GP</div>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($m['is_absent']): ?>
                                <span class="badge bg-secondary">Absent</span>
                            <?php elseif ($m['is_pass']): ?>
                                <span class="badge bg-success">Pass</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Fail</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= e($m['remarks'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#resultsTable').DataTable({ paging: true, pageLength: 50, order: [[0,'asc']] });
    }
});
</script>
