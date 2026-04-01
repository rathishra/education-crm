<?php $pageTitle = 'Attendance Session History'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/attendance') ?>">Attendance Portal</a></li>
                <li class="breadcrumb-item active">Session History</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0"><i class="fas fa-history me-2 text-info"></i>Session History</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/attendance') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Portal
        </a>
        <a href="<?= url('academic/attendance/report') ?>" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-chart-bar me-1"></i> Report
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('academic/attendance/history') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Section</label>
                    <select class="form-select" name="section_id">
                        <option value="">All Sections</option>
                        <?php foreach($sections as $s): ?>
                        <option value="<?= $s['section_id'] ?>" <?= $sectionId == $s['section_id'] ? 'selected' : '' ?>>
                            <?= e($s['program_name']) ?> — Sec <?= e($s['section_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Subject</label>
                    <select class="form-select" name="subject_id">
                        <option value="">All Subjects</option>
                        <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= $subjectId == $sub['id'] ? 'selected' : '' ?>>
                            <?= e($sub['subject_code']) ?> — <?= e($sub['subject_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= url('academic/attendance/history') ?>" class="btn btn-light border" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-secondary"></i>
            <?= count($sessions) ?> Session<?= count($sessions) !== 1 ? 's' : '' ?> Found
        </h6>
        <?php if(count($sessions) >= 200): ?>
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Showing first 200 — narrow your filters</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if(!empty($sessions)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="historyTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Faculty</th>
                        <th class="text-center">Present / Total</th>
                        <th class="text-center">%</th>
                        <th>Type</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sessions as $sess): ?>
                    <?php
                        $pct  = ($sess['total_count'] > 0) ? round($sess['present_count'] / $sess['total_count'] * 100) : 0;
                        $pcls = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold small"><?= date('d M Y', strtotime($sess['attendance_date'])) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= date('l', strtotime($sess['attendance_date'])) ?></div>
                        </td>
                        <td class="small">
                            <span class="badge bg-dark px-2"><?= e($sess['section_name']) ?></span>
                            <div class="text-muted mt-1" style="font-size:.72rem"><?= e($sess['program_name']) ?></div>
                        </td>
                        <td class="small">
                            <div class="fw-bold"><?= e($sess['subject_name']) ?></div>
                            <div class="text-muted"><?= e($sess['subject_code']) ?></div>
                        </td>
                        <td class="small text-muted"><?= e($sess['faculty_name']) ?></td>
                        <td class="text-center">
                            <span class="fw-bold"><?= $sess['present_count'] ?></span>
                            <span class="text-muted">/<?= $sess['total_count'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $pcls ?>-subtle text-<?= $pcls ?> border border-<?= $pcls ?>-subtle">
                                <?= $pct ?>%
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">
                                <?= ucfirst($sess['session_type'] ?? 'lecture') ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?>-subtle text-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?> border border-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?>-subtle">
                                <i class="fas fa-<?= $sess['status'] === 'submitted' ? 'check-circle' : 'edit' ?> me-1"></i>
                                <?= ucfirst($sess['status']) ?>
                            </span>
                        </td>
                        <td class="pe-3">
                            <a href="<?= url('academic/attendance/mark?section_id=' . $sess['section_id'] . '&subject_id=' . $sess['subject_id'] . '&date=' . $sess['attendance_date']) ?>"
                                class="btn btn-sm btn-light border" title="View / Edit">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-clipboard fa-3x mb-3 opacity-25 d-block"></i>
            <h6>No sessions match your filters</h6>
            <p class="small">Try removing some filters or check a different date range.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if(typeof jQuery !== 'undefined' && $.fn.DataTable) {
        $('#historyTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: [8] }]
        });
    }
});
</script>
