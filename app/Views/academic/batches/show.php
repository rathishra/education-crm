<?php $pageTitle = 'Batch: ' . e($batch['program_name']); ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/batches') ?>">Batches</a></li>
                <li class="breadcrumb-item active"><?= e($batch['program_name']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0"><?= e($batch['program_name']) ?>
            <span class="badge bg-secondary ms-2 fs-6"><?= e($batch['batch_term']) ?></span>
            <?php if($batch['status'] === 'active'): ?>
                <span class="badge bg-success ms-1 fs-6">Active</span>
            <?php elseif($batch['status'] === 'graduated'): ?>
                <span class="badge bg-info ms-1 fs-6">Graduated</span>
            <?php else: ?>
                <span class="badge bg-danger ms-1 fs-6">Inactive</span>
            <?php endif; ?>
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/sections/create') ?>" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Add Section
        </a>
        <a href="<?= url('academic/batches/edit/' . $batch['id']) ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle p-3"><i class="fas fa-layer-group text-primary fa-lg"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-primary"><?= count($sections) ?></div>
                    <div class="text-muted small">Total Sections</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle p-3"><i class="fas fa-users text-success fa-lg"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-success"><?= array_sum(array_column($sections, 'enrolled_count')) ?></div>
                    <div class="text-muted small">Total Enrolled</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info-subtle p-3"><i class="fas fa-graduation-cap text-info fa-lg"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-info"><?= $batch['total_semesters'] ?></div>
                    <div class="text-muted small">Semesters</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning-subtle p-3"><i class="fas fa-user-graduate text-warning fa-lg"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-warning"><?= $batch['max_intake'] ?></div>
                    <div class="text-muted small">Max Intake</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sections -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i>Class Sections</h6>
                <a href="<?= url('academic/sections/create') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> New Section
                </a>
            </div>
            <div class="card-body p-0">
                <?php if(!empty($sections)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Section</th>
                                <th>Classroom</th>
                                <th>Advisor</th>
                                <th class="text-center">Enrolled</th>
                                <th class="text-center">Timetable</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($sections as $s): ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?= url('academic/sections/' . $s['id']) ?>" class="fw-bold text-decoration-none text-dark">
                                    <span class="badge bg-dark px-2 py-1 me-1"><?= e($s['section_name']) ?></span>
                                </a>
                                <span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>-subtle text-<?= $s['status'] === 'active' ? 'success' : 'danger' ?> border border-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>-subtle small">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= $s['room_number'] ? e($s['room_number']) : '—' ?></td>
                            <td class="text-muted small"><?= $s['advisor_name'] ? e($s['advisor_name']) : '—' ?></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><?= (int)$s['enrolled_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= (int)$s['timetable_slots'] ?> slots</span>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="<?= url('academic/sections/' . $s['id']) ?>" class="btn btn-sm btn-light border" title="View"><i class="fas fa-eye text-primary"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-layer-group fa-2x mb-2 d-block opacity-25"></i>
                    No sections created for this batch yet.
                    <div class="mt-2"><a href="<?= url('academic/sections/create') ?>" class="btn btn-sm btn-primary">Create First Section</a></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Batch Details & Recent Sessions -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-secondary"></i>Batch Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Start Date</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($batch['start_date'])) ?></dd>
                    <?php if($batch['end_date']): ?>
                    <dt class="col-5 text-muted">End Date</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($batch['end_date'])) ?></dd>
                    <?php endif; ?>
                    <dt class="col-5 text-muted">Semesters</dt>
                    <dd class="col-7"><?= $batch['total_semesters'] ?></dd>
                    <dt class="col-5 text-muted">Max Intake</dt>
                    <dd class="col-7"><?= $batch['max_intake'] ?> students</dd>
                    <?php if(!empty($batch['graduation_credits_required'])): ?>
                    <dt class="col-5 text-muted">Credits Req.</dt>
                    <dd class="col-7"><?= $batch['graduation_credits_required'] ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Recent Attendance Sessions -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2 text-success"></i>Recent Attendance</h6>
                <a href="<?= url('academic/attendance/history?batch_id=' . $batch['id']) ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if(!empty($recentSessions)): foreach($recentSessions as $sess): ?>
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold small"><?= e($sess['subject_name']) ?></div>
                            <div class="text-muted" style="font-size:.78rem"><?= e($sess['section_name']) ?> · <?= e($sess['faculty_name']) ?></div>
                            <div class="text-muted" style="font-size:.78rem"><?= date('d M Y', strtotime($sess['attendance_date'])) ?></div>
                        </div>
                        <div class="text-end">
                            <?php
                                $pct = $sess['total_count'] > 0 ? round($sess['present_count'] / $sess['total_count'] * 100) : 0;
                                $cls = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            ?>
                            <span class="badge bg-<?= $cls ?>-subtle text-<?= $cls ?> border border-<?= $cls ?>-subtle">
                                <?= $sess['present_count'] ?>/<?= $sess['total_count'] ?> (<?= $pct ?>%)
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="list-group-item text-center text-muted py-4 small">No attendance sessions yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
