<?php
$pageTitle = e($member['first_name'].' '.$member['last_name']).' — Faculty Profile';
$initials  = strtoupper(substr($member['first_name'],0,1).substr($member['last_name'],0,1));
$colors    = ['4f46e5','0891b2','059669','d97706','dc2626','7c3aed','db2777','0284c7'];
$color     = $colors[crc32($member['email']) % count($colors)];
$expYrs    = $member['total_experience_months'] ? round($member['total_experience_months']/12,1) : 0;

function starBadge(int $val, int $max = 5): string {
    $html = '';
    for ($i = 1; $i <= $max; $i++) {
        $html .= $i <= $val
            ? '<i class="fas fa-star text-warning" style="font-size:.75rem"></i>'
            : '<i class="far fa-star text-secondary" style="font-size:.75rem"></i>';
    }
    return $html;
}
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-user-circle me-2 text-primary"></i>Faculty Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item active"><?= e($member['first_name'].' '.$member['last_name']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url("faculty/{$member['user_id']}/edit") ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit Profile
        </a>
        <a href="<?= url('faculty') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- ── PROFILE HERO ───────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div style="background:linear-gradient(135deg,#<?= $color ?>,#<?= $color ?>99);height:80px"></div>
    <div class="card-body pt-0">
        <div class="d-flex flex-wrap gap-4 align-items-end" style="margin-top:-40px">
            <!-- Avatar -->
            <?php if ($member['profile_photo']): ?>
                <img src="<?= e($member['profile_photo']) ?>" class="rounded-circle border border-4 border-white shadow"
                     width="80" height="80" style="object-fit:cover">
            <?php else: ?>
                <div class="rounded-circle border border-4 border-white shadow d-flex align-items-center justify-content-center fw-bold text-white fs-3"
                     style="width:80px;height:80px;background:#<?= $color ?>;flex-shrink:0">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            <!-- Info -->
            <div class="flex-grow-1 pb-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="mb-0 fw-bold"><?= e($member['first_name'].' '.$member['last_name']) ?></h3>
                    <span class="badge bg-<?= $member['is_active'] ? 'success' : 'danger' ?>">
                        <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    <?php foreach ($roles as $role): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><?= e($role) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="text-muted"><?= e($member['designation'] ?: 'No Designation') ?>
                    <?php if ($member['department_name']): ?>
                        &nbsp;·&nbsp;<?= e($member['department_name']) ?>
                    <?php endif; ?>
                </div>
                <?php if ($member['employee_id']): ?>
                    <span class="badge bg-light text-secondary border" style="font-size:.7rem">ID: <?= e($member['employee_id']) ?></span>
                <?php endif; ?>
            </div>
            <!-- Quick stats -->
            <div class="d-flex gap-3 text-center pb-1">
                <div>
                    <div class="fs-5 fw-bold text-primary"><?= count($allocations) ?></div>
                    <div class="text-muted" style="font-size:.7rem">SUBJECTS</div>
                </div>
                <div>
                    <div class="fs-5 fw-bold text-success"><?= $totalHours ?></div>
                    <div class="text-muted" style="font-size:.7rem">HRS/WK</div>
                </div>
                <div>
                    <div class="fs-5 fw-bold text-info"><?= $expYrs ?></div>
                    <div class="text-muted" style="font-size:.7rem">EXP YRS</div>
                </div>
                <div>
                    <div class="fs-5 fw-bold text-warning"><?= (int)($member['publications_count'] ?? 0) ?></div>
                    <div class="text-muted" style="font-size:.7rem">PUBS</div>
                </div>
            </div>
        </div>

        <!-- Bio -->
        <?php if ($member['bio']): ?>
        <div class="mt-3 p-3 bg-light rounded small text-muted">
            <i class="fas fa-quote-left me-1 opacity-50"></i><?= e($member['bio']) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── TABS ───────────────────────────────────────────────────── -->
<ul class="nav nav-tabs mb-0" id="profileTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabOverview"><i class="fas fa-id-card me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTeaching"><i class="fas fa-book me-1"></i>Teaching <span class="badge bg-primary rounded-pill ms-1"><?= count($allocations) ?></span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabLeave"><i class="fas fa-calendar-alt me-1"></i>Leaves <span class="badge bg-secondary rounded-pill ms-1"><?= count($leaves) ?></span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPerformance"><i class="fas fa-star me-1"></i>Performance <span class="badge bg-warning text-dark rounded-pill ms-1"><?= count($reviews) ?></span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAttendance"><i class="fas fa-clipboard-check me-1"></i>Attendance</a></li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm mb-4">

    <!-- ── OVERVIEW ───────────────────────────────────────────── -->
    <div class="tab-pane fade show active p-4" id="tabOverview">
        <div class="row g-4">
            <!-- Personal -->
            <div class="col-md-6">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>Personal Information</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7"><?= e($member['email']) ?></dd>
                    <dt class="col-5 text-muted fw-normal">Phone</dt>
                    <dd class="col-7"><?= e($member['phone'] ?: '—') ?></dd>
                    <dt class="col-5 text-muted fw-normal">Member Since</dt>
                    <dd class="col-7"><?= $member['user_since'] ? date('d M Y', strtotime($member['user_since'])) : '—' ?></dd>
                    <?php if ($member['emergency_contact_name']): ?>
                    <dt class="col-5 text-muted fw-normal">Emergency Contact</dt>
                    <dd class="col-7"><?= e($member['emergency_contact_name']) ?> <?= $member['emergency_contact_phone'] ? '(' . e($member['emergency_contact_phone']) . ')' : '' ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
            <!-- Employment -->
            <div class="col-md-6">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fas fa-briefcase me-2"></i>Employment</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Joining Date</dt>
                    <dd class="col-7"><?= $member['joining_date'] ? date('d M Y', strtotime($member['joining_date'])) : '—' ?></dd>
                    <dt class="col-5 text-muted fw-normal">Experience</dt>
                    <dd class="col-7"><?= $expYrs ? $expYrs.' years' : '—' ?></dd>
                    <dt class="col-5 text-muted fw-normal">Department</dt>
                    <dd class="col-7"><?= e($member['department_name'] ?: '—') ?></dd>
                    <dt class="col-5 text-muted fw-normal">Designation</dt>
                    <dd class="col-7"><?= e($member['designation'] ?: '—') ?></dd>
                </dl>
            </div>
            <!-- Academic -->
            <div class="col-md-6">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fas fa-graduation-cap me-2"></i>Academic Credentials</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Qualification</dt>
                    <dd class="col-7"><?= e($member['qualification'] ?: '—') ?></dd>
                    <dt class="col-5 text-muted fw-normal">Specialization</dt>
                    <dd class="col-7"><?= e($member['specialization'] ?: '—') ?></dd>
                    <dt class="col-5 text-muted fw-normal">Publications</dt>
                    <dd class="col-7"><?= (int)($member['publications_count'] ?? 0) ?></dd>
                    <?php if ($member['certifications']): ?>
                    <dt class="col-5 text-muted fw-normal">Certifications</dt>
                    <dd class="col-7"><?= nl2br(e($member['certifications'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
            <!-- Month attendance snapshot -->
            <div class="col-md-6">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="fas fa-calendar-check me-2"></i>This Month Attendance</h6>
                <?php if ($monthStats && ($monthStats['present'] + $monthStats['absent'] + $monthStats['half_day'] + $monthStats['on_leave']) > 0): ?>
                <div class="row g-2 text-center">
                    <?php
                    $attItems = [
                        ['present',  'Present',  'success', $monthStats['present']  ?? 0],
                        ['absent',   'Absent',   'danger',  $monthStats['absent']   ?? 0],
                        ['half_day', 'Half Day', 'warning', $monthStats['half_day'] ?? 0],
                        ['on_leave', 'On Leave', 'info',    $monthStats['on_leave'] ?? 0],
                    ];
                    foreach ($attItems as [$key,$label,$cl,$val]):
                    ?>
                    <div class="col-3">
                        <div class="border rounded py-2">
                            <div class="fw-bold text-<?= $cl ?>"><?= (int)$val ?></div>
                            <div class="text-muted" style="font-size:.65rem"><?= $label ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted small">No attendance recorded this month.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── TEACHING LOAD ──────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tabTeaching">
        <?php if (empty($allocations)): ?>
            <p class="text-muted text-center py-3"><i class="fas fa-book-open me-2"></i>No subject allocations found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>Program / Batch</th>
                        <th>Section</th>
                        <th>Type</th>
                        <th class="text-center">Credits</th>
                        <th class="text-center">Hrs/Wk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allocations as $a): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($a['subject_name']) ?></div>
                            <div class="text-muted"><?= e($a['subject_code']) ?></div>
                        </td>
                        <td><?= e($a['program_name'] . ' ' . ($a['batch_term'] ?: '')) ?></td>
                        <td><?= e($a['section_name'] ?: '—') ?></td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= ucfirst($a['allocation_type'] ?? 'theory') ?></span></td>
                        <td class="text-center"><?= (int)$a['credits'] ?></td>
                        <td class="text-center fw-semibold"><?= (int)$a['hours_per_week'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">Total Weekly Hours</td>
                        <td class="text-center"><?= $totalHours ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── LEAVE HISTORY ──────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tabLeave">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fas fa-plus me-1"></i>Apply Leave
            </button>
        </div>
        <?php if (empty($leaves)): ?>
            <p class="text-muted text-center py-3">No leave records found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-center">Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $l):
                        $sClasses = ['pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'];
                        $sCls = $sClasses[$l['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><span class="badge bg-light text-dark border"><?= ucfirst($l['leave_type']) ?></span></td>
                        <td><?= date('d M Y', strtotime($l['start_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($l['end_date'])) ?></td>
                        <td class="text-center fw-bold"><?= (int)$l['days'] ?></td>
                        <td class="text-muted"><?= e(strlen($l['reason'] ?? '') > 50 ? substr($l['reason'],0,50).'…' : ($l['reason'] ?? '—')) ?></td>
                        <td><span class="badge bg-<?= $sCls ?>"><?= ucfirst($l['status']) ?></span></td>
                        <td><?= e($l['approved_by_name'] ?: '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── PERFORMANCE ────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tabPerformance">
        <?php if (empty($reviews)): ?>
            <p class="text-muted text-center py-3">No performance reviews found.</p>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reviews as $rv):
                $overall = (float)$rv['overall_rating'];
                $pct     = min(100, $overall / 5 * 100);
                $oCls    = $pct >= 80 ? 'success' : ($pct >= 60 ? 'primary' : ($pct >= 40 ? 'warning' : 'danger'));
            ?>
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><?= e($rv['review_period']) ?></h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?= $oCls ?> fs-6"><?= number_format($overall,1) ?>/5</span>
                                <span class="badge bg-<?= $rv['status']==='acknowledged' ? 'success' : 'secondary' ?>"><?= ucfirst($rv['status']) ?></span>
                            </div>
                        </div>
                        <div class="row g-2 small mb-3">
                            <div class="col-6">
                                <div class="text-muted mb-1">Teaching Quality</div>
                                <?= starBadge((int)$rv['teaching_quality']) ?>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1">Punctuality</div>
                                <?= starBadge((int)$rv['punctuality']) ?>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1">Research</div>
                                <?= starBadge((int)$rv['research_contribution']) ?>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1">Admin Work</div>
                                <?= starBadge((int)$rv['admin_contribution']) ?>
                            </div>
                        </div>
                        <?php if ($rv['student_feedback_score']): ?>
                        <div class="small mb-2"><span class="text-muted">Student Feedback:</span> <strong><?= number_format($rv['student_feedback_score'],1) ?>/5</strong></div>
                        <?php endif; ?>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-<?= $oCls ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <?php if ($rv['comments']): ?>
                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-comment me-1"></i><?= e($rv['comments']) ?></p>
                        <?php endif; ?>
                        <div class="text-end text-muted mt-2" style="font-size:.7rem">Reviewed by: <?= e($rv['reviewer_name'] ?: '—') ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── ATTENDANCE ─────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tabAttendance">
        <?php if (empty($attendance)): ?>
            <p class="text-muted text-center py-3">No attendance records for the last 30 days.</p>
        <?php else: ?>
        <h6 class="fw-semibold mb-3">Last 30 Days</h6>
        <div class="d-flex flex-wrap gap-1 mb-4">
            <?php
            $attColors = ['present'=>'success','absent'=>'danger','half_day'=>'warning','on_leave'=>'info','holiday'=>'secondary'];
            for ($i = 29; $i >= 0; $i--):
                $d   = date('Y-m-d', strtotime("-$i days"));
                $rec = $attendanceMap[$d] ?? null;
                $cls = $rec ? ($attColors[$rec['status']] ?? 'light') : 'light';
                $tip = $rec ? ucwords(str_replace('_',' ',$rec['status'])) . ($rec['check_in'] ? ' ('.$rec['check_in'].')' : '') : 'Not marked';
            ?>
            <div class="rounded" style="width:24px;height:24px;background:var(--bs-<?= $cls ?>);opacity:<?= $rec ? .85 : .15 ?>"
                 title="<?= date('d M', strtotime($d)) ?>: <?= $tip ?>" data-bs-toggle="tooltip"></div>
            <?php endfor; ?>
        </div>
        <div class="d-flex gap-3 small flex-wrap">
            <?php foreach ($attColors as $k=>$c): ?>
            <span><span class="badge bg-<?= $c ?>">&nbsp;</span> <?= ucwords(str_replace('_',' ',$k)) ?></span>
            <?php endforeach; ?>
            <span><span class="rounded" style="width:14px;height:14px;background:var(--bs-secondary);opacity:.15;display:inline-block"></span> Not Marked</span>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /tab-content -->

<!-- ── APPLY LEAVE MODAL ──────────────────────────────────────── -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('faculty/leave/apply') ?>">
                <?= csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $member['user_id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Apply Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-select">
                            <option value="casual">Casual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="earned">Earned Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">From Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">To Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    // Remember active tab
    const key = 'facultyProfileTab_<?= $member['user_id'] ?>';
    const saved = localStorage.getItem(key);
    if (saved) {
        const tab = document.querySelector('[href="' + saved + '"]');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
    document.querySelectorAll('#profileTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => localStorage.setItem(key, e.target.getAttribute('href')));
    });
});
</script>
