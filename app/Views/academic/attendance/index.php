<?php $pageTitle = 'Attendance Portal'; ?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-clipboard-check me-2 text-primary"></i>Faculty Attendance Portal</h4>
        <p class="text-muted mb-0"><?= date('l, d F Y') ?> — Today's Classes</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/attendance/history') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-history me-1"></i> Session History
        </a>
        <a href="<?= url('academic/attendance/report') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-chart-bar me-1"></i> Report
        </a>
    </div>
</div>

<!-- KPI Summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= (int)($stats['today_count'] ?? 0) ?></div>
                <div class="text-muted small">Today's Classes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-success"><?= (int)($stats['submitted_count'] ?? 0) ?></div>
                <div class="text-muted small">Submitted Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-warning"><?= (int)($stats['week_count'] ?? 0) ?></div>
                <div class="text-muted small">This Week</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-info"><?= (int)($stats['total_count'] ?? 0) ?></div>
                <div class="text-muted small">Total Sessions</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Timetable -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-2 text-warning"></i>Today's Schedule — <?= date('l') ?></h6>
            </div>
            <div class="card-body p-0">
                <?php if(!empty($todayClasses)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach($todayClasses as $cls): ?>
                    <?php
                        $sessionId   = $cls['session_posted_id'] ?? null;
                        $sessionStat = $cls['session_status'] ?? null;
                        $isPosted    = !empty($sessionId);
                        $isSubmitted = ($sessionStat === 'submitted');
                    ?>
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-secondary"><?= e($cls['period_name']) ?></span>
                                    <span class="fw-bold"><?= e($cls['subject_name']) ?></span>
                                    <span class="text-muted small">(<?= e($cls['subject_code']) ?>)</span>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-users me-1"></i><?= e($cls['program_name']) ?> — Section <?= e($cls['section_name']) ?>
                                    <span class="mx-2">·</span>
                                    <i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($cls['start_time'])) ?> – <?= date('h:i A', strtotime($cls['end_time'])) ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <?php if($isSubmitted): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-1 d-block">
                                        <i class="fas fa-check-circle me-1"></i>Submitted
                                    </span>
                                    <a href="<?= url('academic/attendance/mark?section_id=' . $cls['section_id'] . '&subject_id=' . $cls['subject_id'] . '&date=' . date('Y-m-d') . '&period_id=' . ($cls['period_id'] ?? '')) ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                <?php elseif($isPosted): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle mb-1 d-block">
                                        <i class="fas fa-edit me-1"></i>Draft
                                    </span>
                                    <a href="<?= url('academic/attendance/mark?section_id=' . $cls['section_id'] . '&subject_id=' . $cls['subject_id'] . '&date=' . date('Y-m-d') . '&period_id=' . ($cls['period_id'] ?? '')) ?>"
                                        class="btn btn-sm btn-warning text-dark">
                                        <i class="fas fa-edit me-1"></i>Continue
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle mb-1 d-block">
                                        <i class="fas fa-exclamation-circle me-1"></i>Pending
                                    </span>
                                    <a href="<?= url('academic/attendance/mark?section_id=' . $cls['section_id'] . '&subject_id=' . $cls['subject_id'] . '&date=' . date('Y-m-d') . '&period_id=' . ($cls['period_id'] ?? '')) ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-clipboard-list me-1"></i>Mark Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-25 d-block"></i>
                    <h6>No classes scheduled for today</h6>
                    <p class="small mb-3">Check your timetable or use the manual form below to mark attendance.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Manual Entry Card -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-keyboard me-2 text-secondary"></i>Manual Attendance Entry</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= url('academic/attendance/mark') ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Section</label>
                            <select class="form-select" name="section_id" required>
                                <option value="">— Select Section —</option>
                                <?php if(!empty($sections)): foreach($sections as $s): ?>
                                <option value="<?= $s['section_id'] ?>"><?= e($s['program_name']) ?> — Sec <?= e($s['section_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Subject</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">— Select Subject —</option>
                                <?php if(!empty($subjects)): foreach($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= e($sub['subject_code']) ?> — <?= e($sub['subject_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Date</label>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" title="Open Register">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Sessions -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-info"></i>Recent Sessions</h6>
                <a href="<?= url('academic/attendance/history') ?>" class="btn btn-sm btn-outline-info">All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if(!empty($recentSessions)): foreach($recentSessions as $sess): ?>
                <a href="<?= url('academic/attendance/mark?section_id=' . $sess['section_id'] . '&subject_id=' . $sess['subject_id'] . '&date=' . $sess['attendance_date']) ?>"
                    class="list-group-item list-group-item-action px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold small"><?= e($sess['subject_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= e($sess['section_name']) ?> · <?= date('d M Y', strtotime($sess['attendance_date'])) ?></div>
                        </div>
                        <div class="text-end">
                            <?php
                                $pct = ($sess['total'] ?? 0) > 0 ? round(($sess['present'] ?? 0) / $sess['total'] * 100) : 0;
                                $cls = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            ?>
                            <span class="badge bg-<?= $cls ?>-subtle text-<?= $cls ?> border border-<?= $cls ?>-subtle d-block mb-1" style="font-size:.7rem">
                                <?= ($sess['present'] ?? 0) ?>/<?= ($sess['total'] ?? 0) ?>
                            </span>
                            <span class="badge bg-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?>-subtle text-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?> border border-<?= $sess['status'] === 'submitted' ? 'success' : 'warning' ?>-subtle" style="font-size:.68rem">
                                <?= ucfirst($sess['status']) ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; else: ?>
                <div class="list-group-item text-center text-muted py-4 small">
                    <i class="fas fa-clipboard fa-2x mb-2 d-block opacity-25"></i>No sessions recorded yet.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
