<?php $pageTitle = 'Mark Attendance'; ?>

<style>
/* ── Attendance Page Styles ───────────────────────────────────── */
.att-filter-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 8px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0;
}
.att-stat {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
}
.att-stat-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.att-stat-val  { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
.att-stat-lbl  { font-size: 0.72rem; color: #64748b; font-weight: 500; }

/* progress header */
.att-progress-header {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
}
.att-progress-bar-wrap { height: 8px; border-radius: 4px; background: #f1f5f9; overflow: hidden; }
.att-progress-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,#059669,#10b981); transition: width .35s ease; }

/* student table */
.att-table { font-size: 0.875rem; }
.att-table thead th {
    background: #f8fafc; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em; color: #475569;
    border-bottom: 2px solid #e2e8f0; padding: .65rem 1rem;
}
.att-table tbody td { padding: .6rem 1rem; vertical-align: middle; border-color: #f1f5f9; }
.att-row { transition: background .1s; }
.att-row:hover { background: #f8fafc !important; }
.att-row.status-present { background: rgba(209,250,229,.35) !important; }
.att-row.status-absent  { background: rgba(254,226,226,.35) !important; }
.att-row.status-late    { background: rgba(254,243,199,.35) !important; }
.att-row.status-half_day{ background: rgba(224,242,254,.35) !important; }
.att-row.focused-row    { outline: 2px solid #3b82f6; outline-offset: -2px; }

/* avatar */
.att-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: #fff; flex-shrink: 0;
}

/* radio buttons */
.att-btn-present, .att-btn-absent, .att-btn-late, .att-btn-half {
    border-radius: 8px; font-size: 0.78rem; font-weight: 600;
    padding: .3rem .75rem; transition: all .12s;
    border: 1.5px solid transparent; cursor: pointer;
    white-space: nowrap;
}
.att-btn-present  { color: #059669; background: #f0fdf4; border-color: #a7f3d0; }
.att-btn-absent   { color: #dc2626; background: #fff5f5; border-color: #fca5a5; }
.att-btn-late     { color: #d97706; background: #fffbeb; border-color: #fcd34d; }
.att-btn-half     { color: #0891b2; background: #f0f9ff; border-color: #a5f3fc; }
.btn-check:checked + .att-btn-present { background: #059669; color: #fff; border-color: #059669; box-shadow: 0 3px 8px rgba(5,150,105,.3); }
.btn-check:checked + .att-btn-absent  { background: #dc2626; color: #fff; border-color: #dc2626; box-shadow: 0 3px 8px rgba(220,38,38,.3); }
.btn-check:checked + .att-btn-late    { background: #d97706; color: #fff; border-color: #d97706; box-shadow: 0 3px 8px rgba(217,119,6,.3); }
.btn-check:checked + .att-btn-half    { background: #0891b2; color: #fff; border-color: #0891b2; box-shadow: 0 3px 8px rgba(8,145,178,.3); }

/* quick-action buttons */
.btn-att-quick {
    font-size: 0.78rem; font-weight: 600; border-radius: 8px;
    padding: .3rem .85rem; border: 1.5px solid; transition: all .12s;
}
.btn-att-quick.green  { color: #059669; border-color: #a7f3d0; background: #f0fdf4; }
.btn-att-quick.green:hover  { background: #059669; color: #fff; border-color: #059669; }
.btn-att-quick.red    { color: #dc2626; border-color: #fca5a5; background: #fff5f5; }
.btn-att-quick.red:hover    { background: #dc2626; color: #fff; border-color: #dc2626; }
.btn-att-quick.amber  { color: #d97706; border-color: #fcd34d; background: #fffbeb; }
.btn-att-quick.amber:hover  { background: #d97706; color: #fff; border-color: #d97706; }

/* sticky save bar */
.att-save-bar {
    position: sticky; bottom: 0; z-index: 50;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: .75rem 1.25rem;
    border-radius: 0 0 14px 14px;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}

/* search input */
.att-search-wrap { position: relative; }
.att-search-wrap input { padding-left: 2rem; font-size: .8rem; border-radius: 8px; border: 1.5px solid #e2e8f0; }
.att-search-wrap i { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .8rem; }

/* keyboard hint badge */
.kbd-badge {
    display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 4px; padding: 1px 5px; font-size: .65rem;
    color: #64748b; font-family: monospace;
}

/* mobile card view */
@media (max-width: 767.98px) {
    .att-desktop-table { display: none; }
    .att-mobile-cards  { display: block; }
}
@media (min-width: 768px) {
    .att-desktop-table { display: block; }
    .att-mobile-cards  { display: none; }
}
.att-mobile-card {
    background: #fff; border-radius: 10px; border: 1px solid #e2e8f0;
    padding: .875rem; margin-bottom: .5rem;
}
.att-mobile-card.status-present { border-left: 3px solid #059669; }
.att-mobile-card.status-absent  { border-left: 3px solid #dc2626; }
.att-mobile-card.status-late    { border-left: 3px solid #d97706; }
.att-mobile-card.status-half_day{ border-left: 3px solid #0891b2; }
</style>

<!-- ── PAGE HEADER ──────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-calendar-check me-2 text-primary"></i>Mark Attendance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Mark Attendance</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('attendance.reports')): ?>
        <a href="<?= url('attendance/report') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-bar me-1"></i>Monthly Report
        </a>
        <?php endif; ?>
        <div class="text-muted small d-none d-md-flex align-items-center gap-1">
            <i class="fas fa-keyboard opacity-50"></i>
            <span class="kbd-badge">P</span>Present
            <span class="kbd-badge ms-1">A</span>Absent
            <span class="kbd-badge ms-1">L</span>Late
            <span class="kbd-badge ms-1">H</span>Half
        </div>
    </div>
</div>

<!-- ── FILTER CARD ──────────────────────────────────────────────── -->
<div class="att-filter-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('attendance') ?>" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Course <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="course_id" id="filterCourse" required onchange="this.form.submit()">
                        <option value="">— Course —</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Batch <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="batch_id" required onchange="this.form.submit()">
                        <option value="">— Batch —</option>
                        <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Section</label>
                    <select class="form-select form-select-sm" name="section_id">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= $sectionId == $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm" name="date" value="<?= e($date) ?>" required max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Subject <span class="text-muted fw-normal">(optional)</span></label>
                    <select class="form-select form-select-sm" name="subject_id">
                        <option value="">Daily Attendance</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $subjectId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> <?= !empty($s['code']) ? '(' . e($s['code']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId && $date): ?>

<!-- ── STATS BAR ────────────────────────────────────────────────── -->
<?php if (!empty($students)): ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="att-stat">
            <div class="att-stat-icon" style="background:#ede9fe;color:#7c3aed"><i class="fas fa-users"></i></div>
            <div>
                <div class="att-stat-val" id="cntTotal"><?= count($students) ?></div>
                <div class="att-stat-lbl">Total Students</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="att-stat">
            <div class="att-stat-icon" style="background:#d1fae5;color:#059669"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="att-stat-val text-success" id="cntPresent">0</div>
                <div class="att-stat-lbl">Present</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="att-stat">
            <div class="att-stat-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="att-stat-val text-danger" id="cntAbsent">0</div>
                <div class="att-stat-lbl">Absent</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="att-stat">
            <div class="att-stat-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-clock"></i></div>
            <div>
                <div class="att-stat-val text-warning" id="cntOther">0</div>
                <div class="att-stat-lbl">Late / Half Day</div>
            </div>
        </div>
    </div>
</div>

<!-- Progress bar -->
<div class="att-progress-header mb-3">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <div class="small fw-semibold text-muted">Marking Progress</div>
        <div class="small fw-bold text-primary" id="progressText">0 / <?= count($students) ?> marked</div>
    </div>
    <div class="att-progress-bar-wrap">
        <div class="att-progress-bar-fill" id="progressBar" style="width:0%"></div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($students)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3" style="font-size:3rem;opacity:.2"><i class="fas fa-user-slash"></i></div>
        <h6 class="fw-semibold text-muted">No Active Students</h6>
        <p class="text-muted small mb-0">No students found in the selected batch<?= $sectionId ? '/section' : '' ?>.</p>
    </div>
</div>

<?php else: ?>
<form method="POST" action="<?= url('attendance/store') ?>" id="attForm">
    <?= csrfField() ?>
    <input type="hidden" name="course_id"  value="<?= e($courseId) ?>">
    <input type="hidden" name="batch_id"   value="<?= e($batchId) ?>">
    <input type="hidden" name="date"       value="<?= e($date) ?>">
    <input type="hidden" name="subject_id" value="<?= e($subjectId) ?>">
    <input type="hidden" name="section_id" value="<?= e($sectionId) ?>">

    <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden">
        <!-- Card Header -->
        <div class="card-header bg-white border-bottom py-2 px-3">
            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">
                <!-- Left: date + badges -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="fw-bold" style="font-size:.95rem">
                        <i class="fas fa-calendar-day me-1 text-primary opacity-75"></i>
                        <?= date('l, d M Y', strtotime($date)) ?>
                    </div>
                    <?php if ($subjectId): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem">Subject-wise</span>
                    <?php endif; ?>
                    <?php if ($sectionId): ?>
                    <?php $secName = array_column($sections, 'name', 'id')[$sectionId] ?? ''; ?>
                    <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.7rem">Section: <?= e($secName) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($existingAttendance)): ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.7rem"><i class="fas fa-history me-1"></i>Editing saved record</span>
                    <?php endif; ?>
                </div>

                <!-- Right: quick actions + search -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="att-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="studentSearch" class="form-control form-control-sm" placeholder="Search student…" style="width:160px">
                    </div>
                    <button type="button" class="btn-att-quick green mark-all" data-status="present">
                        <i class="fas fa-check me-1"></i>All Present
                    </button>
                    <button type="button" class="btn-att-quick red mark-all" data-status="absent">
                        <i class="fas fa-times me-1"></i>All Absent
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-radius:8px;font-size:.78rem">
                            More
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:10px;min-width:200px">
                            <li><h6 class="dropdown-header">Mark Remaining As</h6></li>
                            <li><button type="button" class="dropdown-item small" onclick="markRemaining('present')"><i class="fas fa-check text-success me-2"></i>Mark Unmarked → Present</button></li>
                            <li><button type="button" class="dropdown-item small" onclick="markRemaining('absent')"><i class="fas fa-times text-danger me-2"></i>Mark Unmarked → Absent</button></li>
                            <li><button type="button" class="dropdown-item small" onclick="markRemaining('late')"><i class="fas fa-clock text-warning me-2"></i>Mark Unmarked → Late</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button type="button" class="dropdown-item small" onclick="clearAll()"><i class="fas fa-undo text-secondary me-2"></i>Reset All</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── DESKTOP TABLE ─────────────────────────────────── -->
        <div class="att-desktop-table">
            <div class="table-responsive">
                <table class="table att-table mb-0" id="attTable">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Student</th>
                            <th style="width:340px">
                                Status
                                <span class="text-muted fw-normal ms-2 d-none d-lg-inline" style="font-size:.65rem;text-transform:none;letter-spacing:0">
                                    (keyboard: <span class="kbd-badge">P</span> <span class="kbd-badge">A</span> <span class="kbd-badge">L</span> <span class="kbd-badge">H</span>)
                                </span>
                            </th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="attBody">
                        <?php foreach ($students as $i => $stu):
                            $att     = $existingAttendance[$stu['id']] ?? null;
                            $status  = $att['status'] ?? '';
                            $remarks = $att['remarks'] ?? '';
                            $initials = strtoupper(substr($stu['first_name'],0,1) . substr($stu['last_name'] ?? '',0,1));
                            $avatarColors = ['#059669','#3b82f6','#7c3aed','#d97706','#0891b2','#db2777'];
                            $avatarBg = $avatarColors[$i % count($avatarColors)];
                        ?>
                        <tr class="att-row <?= $status ? 'status-' . $status : '' ?>" data-name="<?= strtolower(e($stu['first_name'] . ' ' . $stu['last_name'] . ' ' . $stu['student_id_number'])) ?>">
                            <td class="text-muted fw-semibold" style="font-size:.78rem"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="att-avatar" style="background:<?= $avatarBg ?>"><?= $initials ?></div>
                                    <div>
                                        <div class="fw-semibold"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></div>
                                        <div class="text-muted" style="font-size:.72rem">
                                            <?= e($stu['student_id_number']) ?>
                                            <?php if (!empty($stu['roll_number'])): ?>
                                            &bull; Roll <?= e($stu['roll_number']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="p_<?= $stu['id'] ?>" value="present" <?= $status === 'present' ? 'checked' : '' ?>>
                                    <label class="att-btn-present" for="p_<?= $stu['id'] ?>"><i class="fas fa-check me-1"></i>Present</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="a_<?= $stu['id'] ?>" value="absent" <?= $status === 'absent' ? 'checked' : '' ?>>
                                    <label class="att-btn-absent" for="a_<?= $stu['id'] ?>"><i class="fas fa-times me-1"></i>Absent</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="l_<?= $stu['id'] ?>" value="late" <?= $status === 'late' ? 'checked' : '' ?>>
                                    <label class="att-btn-late" for="l_<?= $stu['id'] ?>"><i class="fas fa-clock me-1"></i>Late</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="h_<?= $stu['id'] ?>" value="half_day" <?= $status === 'half_day' ? 'checked' : '' ?>>
                                    <label class="att-btn-half" for="h_<?= $stu['id'] ?>"><i class="fas fa-adjust me-1"></i>Half</label>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="remarks[<?= $stu['id'] ?>]" value="<?= e($remarks) ?>" placeholder="Optional note…" style="border-radius:8px;font-size:.8rem">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MOBILE CARDS ──────────────────────────────────── -->
        <div class="att-mobile-cards p-2" id="mobileCards">
            <?php foreach ($students as $i => $stu):
                $att     = $existingAttendance[$stu['id']] ?? null;
                $status  = $att['status'] ?? '';
                $remarks = $att['remarks'] ?? '';
                $initials = strtoupper(substr($stu['first_name'],0,1) . substr($stu['last_name'] ?? '',0,1));
                $avatarColors = ['#059669','#3b82f6','#7c3aed','#d97706','#0891b2','#db2777'];
                $avatarBg = $avatarColors[$i % count($avatarColors)];
            ?>
            <div class="att-mobile-card <?= $status ? 'status-' . $status : '' ?>" data-name="<?= strtolower(e($stu['first_name'] . ' ' . $stu['last_name'] . ' ' . $stu['student_id_number'])) ?>">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="att-avatar" style="background:<?= $avatarBg ?>"><?= $initials ?></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= e($stu['student_id_number']) ?></div>
                    </div>
                    <span class="mobile-status-badge badge" id="mob_badge_<?= $stu['id'] ?>"></span>
                </div>
                <div class="d-flex gap-1 flex-wrap mb-2">
                    <input type="radio" class="btn-check att-radio-mob" name="attendance[<?= $stu['id'] ?>]" id="mp_<?= $stu['id'] ?>" value="present" <?= $status === 'present' ? 'checked' : '' ?>>
                    <label class="att-btn-present" for="mp_<?= $stu['id'] ?>"><i class="fas fa-check me-1"></i>Present</label>

                    <input type="radio" class="btn-check att-radio-mob" name="attendance[<?= $stu['id'] ?>]" id="ma_<?= $stu['id'] ?>" value="absent" <?= $status === 'absent' ? 'checked' : '' ?>>
                    <label class="att-btn-absent" for="ma_<?= $stu['id'] ?>"><i class="fas fa-times me-1"></i>Absent</label>

                    <input type="radio" class="btn-check att-radio-mob" name="attendance[<?= $stu['id'] ?>]" id="ml_<?= $stu['id'] ?>" value="late" <?= $status === 'late' ? 'checked' : '' ?>>
                    <label class="att-btn-late" for="ml_<?= $stu['id'] ?>"><i class="fas fa-clock me-1"></i>Late</label>

                    <input type="radio" class="btn-check att-radio-mob" name="attendance[<?= $stu['id'] ?>]" id="mh_<?= $stu['id'] ?>" value="half_day" <?= $status === 'half_day' ? 'checked' : '' ?>>
                    <label class="att-btn-half" for="mh_<?= $stu['id'] ?>"><i class="fas fa-adjust me-1"></i>Half</label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── STICKY SAVE BAR ───────────────────────────────── -->
        <?php if (hasPermission('attendance.mark')): ?>
        <div class="att-save-bar">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="text-white-50 small d-none d-md-block">
                    <i class="fas fa-info-circle me-1"></i>Saving replaces existing attendance for this date
                </div>
                <div class="d-flex gap-3">
                    <span class="small text-white"><span class="fw-bold text-success" id="savePresent">0</span> Present</span>
                    <span class="small text-white"><span class="fw-bold text-danger" id="saveAbsent">0</span> Absent</span>
                    <span class="small text-white"><span class="fw-bold text-warning" id="saveOther">0</span> Other</span>
                </div>
            </div>
            <button type="submit" class="btn btn-success px-4 fw-bold" id="saveAttBtn" style="border-radius:10px">
                <i class="fas fa-save me-2"></i>Save Attendance
            </button>
        </div>
        <?php endif; ?>
    </div>
</form>
<?php endif; ?>

<?php else: ?>
<!-- ── NO SELECTION STATE ────────────────────────────────────── -->
<div class="card border-0 shadow-sm" style="border-radius:14px">
    <div class="card-body text-center py-5">
        <div class="mb-3" style="font-size:4rem;opacity:.12"><i class="fas fa-calendar-check"></i></div>
        <h5 class="fw-bold text-muted mb-1">Select Class & Date</h5>
        <p class="text-muted small mb-4">Choose a course, batch and date above to load students and mark attendance.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <div class="text-muted small"><span class="badge bg-light text-dark border me-1">1</span>Select Course</div>
            <i class="fas fa-arrow-right text-muted opacity-50 align-self-center"></i>
            <div class="text-muted small"><span class="badge bg-light text-dark border me-1">2</span>Select Batch</div>
            <i class="fas fa-arrow-right text-muted opacity-50 align-self-center"></i>
            <div class="text-muted small"><span class="badge bg-light text-dark border me-1">3</span>Pick Date</div>
            <i class="fas fa-arrow-right text-muted opacity-50 align-self-center"></i>
            <div class="text-muted small"><span class="badge bg-primary text-white border me-1">4</span>Mark & Save</div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const TOTAL = <?= count($students ?? []) ?>;
let focusedRow = -1;

// ── Count updater ─────────────────────────────────────────────
function updateCounts() {
    let present = 0, absent = 0, other = 0, marked = 0;
    document.querySelectorAll('.att-radio:checked, .att-radio-mob:checked').forEach(r => {
        // Deduplicate (desktop + mobile share same name)
        if (r.closest('#attBody') || r.closest('#mobileCards')) {
            if (r.value === 'present') present++;
            else if (r.value === 'absent') absent++;
            else other++;
            marked++;
        }
    });
    // Actually count by name (avoid double counting desktop+mobile)
    present = 0; absent = 0; other = 0; marked = 0;
    const seen = new Set();
    document.querySelectorAll('.att-radio:checked').forEach(r => {
        const name = r.name;
        if (!seen.has(name)) {
            seen.add(name);
            if (r.value === 'present') present++;
            else if (r.value === 'absent') absent++;
            else other++;
            marked++;
        }
    });
    // Also count mobile (they share same `name` so radio group is synced)

    const set = v => { if(v) v.textContent = arguments[0]; };
    ['cntPresent','cntAbsent','cntOther'].forEach((id,i) =>
        document.getElementById(id) && (document.getElementById(id).textContent = [present,absent,other][i]));
    ['savePresent','saveAbsent','saveOther'].forEach((id,i) =>
        document.getElementById(id) && (document.getElementById(id).textContent = [present,absent,other][i]));

    const pct = TOTAL > 0 ? Math.round(marked / TOTAL * 100) : 0;
    const pb  = document.getElementById('progressBar');
    const pt  = document.getElementById('progressText');
    if (pb) pb.style.width = pct + '%';
    if (pt) pt.textContent = marked + ' / ' + TOTAL + ' marked';
    if (pb) pb.style.background = pct === 100 ? 'linear-gradient(90deg,#059669,#10b981)' : 'linear-gradient(90deg,#3b82f6,#60a5fa)';

    // Row highlight
    document.querySelectorAll('.att-row').forEach(row => {
        row.className = row.className.replace(/\bstatus-\S+/g, '').trim();
        const checked = row.querySelector('.att-radio:checked');
        if (checked) row.classList.add('status-' + checked.value);
    });
    document.querySelectorAll('.att-mobile-card').forEach(card => {
        card.className = card.className.replace(/\bstatus-\S+/g, '').trim();
        const checked = card.querySelector('.att-radio-mob:checked');
        if (checked) card.classList.add('status-' + checked.value);
    });
}

// ── Mark all ─────────────────────────────────────────────────
document.querySelectorAll('.mark-all').forEach(btn => {
    btn.addEventListener('click', function () {
        const s = this.dataset.status;
        document.querySelectorAll(`.att-radio[value="${s}"]`).forEach(r => r.checked = true);
        updateCounts();
    });
});

// ── Mark remaining ────────────────────────────────────────────
function markRemaining(status) {
    document.querySelectorAll('.att-radio').forEach(r => {
        const name  = r.name;
        const group = document.querySelectorAll(`.att-radio[name="${name}"]:checked`);
        if (!group.length) {
            if (r.value === status) r.checked = true;
        }
    });
    updateCounts();
}

function clearAll() {
    document.querySelectorAll('.att-radio, .att-radio-mob').forEach(r => r.checked = false);
    updateCounts();
}

// ── Radio change ──────────────────────────────────────────────
document.querySelectorAll('.att-radio, .att-radio-mob').forEach(r => {
    r.addEventListener('change', updateCounts);
});

// ── Keyboard shortcuts ────────────────────────────────────────
const keyMap = { p: 'present', a: 'absent', l: 'late', h: 'half_day' };
const rows   = Array.from(document.querySelectorAll('.att-row'));

document.addEventListener('keydown', e => {
    if (['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) return;
    const key = e.key.toLowerCase();
    if (keyMap[key] && focusedRow >= 0 && focusedRow < rows.length) {
        const row = rows[focusedRow];
        const radio = row.querySelector(`.att-radio[value="${keyMap[key]}"]`);
        if (radio) { radio.checked = true; updateCounts(); }
        if (key !== 'h') { // auto-advance on P/A/L
            rows[focusedRow]?.classList.remove('focused-row');
            focusedRow = Math.min(focusedRow + 1, rows.length - 1);
            rows[focusedRow]?.classList.add('focused-row');
            rows[focusedRow]?.scrollIntoView({ block: 'nearest' });
        }
    }
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        rows[focusedRow]?.classList.remove('focused-row');
        focusedRow = e.key === 'ArrowDown'
            ? Math.min(focusedRow + 1, rows.length - 1)
            : Math.max(focusedRow - 1, 0);
        rows[focusedRow]?.classList.add('focused-row');
        rows[focusedRow]?.scrollIntoView({ block: 'nearest' });
    }
});

// Click row to focus
rows.forEach((row, idx) => {
    row.addEventListener('click', () => {
        rows[focusedRow]?.classList.remove('focused-row');
        focusedRow = idx;
        row.classList.add('focused-row');
    });
});

// ── Student search ────────────────────────────────────────────
document.getElementById('studentSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.att-row').forEach(row => {
        row.style.display = row.dataset.name?.includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.att-mobile-card').forEach(card => {
        card.style.display = card.dataset.name?.includes(q) ? '' : 'none';
    });
});

// ── Save button loading ───────────────────────────────────────
document.getElementById('attForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('saveAttBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" style="width:.9rem;height:.9rem;border-width:2px"></span>Saving…';
    }
});

// Init
updateCounts();
if (rows.length) { focusedRow = 0; rows[0].classList.add('focused-row'); }
</script>
