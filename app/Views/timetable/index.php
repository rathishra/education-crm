<?php $pageTitle = 'Timetable'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-alt me-2 text-primary"></i>Timetable</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Timetable</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('timetable.manage')): ?>
        <a href="<?= url('timetable/create') ?><?= $batchId ? '?course_id='.$courseId : '' ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Period
        </a>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i>Select Course & Batch
    </div>
    <div class="card-body">
        <form method="GET" action="<?= url('timetable') ?>" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" required onchange="this.form.submit()">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                    <select class="form-select" name="batch_id" required onchange="this.form.submit()">
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-eye me-1"></i>View
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!$batchId): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <div style="font-size:3.5rem;opacity:.18;margin-bottom:.75rem;"><i class="fas fa-calendar-alt"></i></div>
        <h5 class="text-muted fw-semibold mb-1">Select a Batch to View Schedule</h5>
        <p class="text-muted small mb-0">Choose a course and batch above to view or manage the weekly timetable.</p>
    </div>
</div>
<?php else: ?>

<?php
// Day meta: color accent, icon
$dayMeta = [
    'monday'    => ['color' => '#4f46e5', 'bg' => '#eef2ff', 'label' => 'Monday',    'short' => 'MON', 'icon' => 'fas fa-calendar-day'],
    'tuesday'   => ['color' => '#0891b2', 'bg' => '#f0f9ff', 'label' => 'Tuesday',   'short' => 'TUE', 'icon' => 'fas fa-calendar-day'],
    'wednesday' => ['color' => '#059669', 'bg' => '#f0fdf4', 'label' => 'Wednesday', 'short' => 'WED', 'icon' => 'fas fa-calendar-day'],
    'thursday'  => ['color' => '#7c3aed', 'bg' => '#f5f3ff', 'label' => 'Thursday',  'short' => 'THU', 'icon' => 'fas fa-calendar-day'],
    'friday'    => ['color' => '#d97706', 'bg' => '#fffbeb', 'label' => 'Friday',    'short' => 'FRI', 'icon' => 'fas fa-calendar-day'],
    'saturday'  => ['color' => '#db2777', 'bg' => '#fdf2f8', 'label' => 'Saturday',  'short' => 'SAT', 'icon' => 'fas fa-calendar-day'],
    'sunday'    => ['color' => '#94a3b8', 'bg' => '#f8fafc', 'label' => 'Sunday',    'short' => 'SUN', 'icon' => 'fas fa-calendar-day'],
];

$totalPeriods = array_sum(array_map('count', $timetable));
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalPeriods ?></div>
                <div class="stat-label">Total Periods / Week</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-emerald py-3">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-body">
                <?php $activeDays = count(array_filter($timetable, fn($p) => !empty($p))); ?>
                <div class="stat-value"><?= $activeDays ?></div>
                <div class="stat-label">Working Days</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-sky py-3">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-body">
                <?php $uniqueSubjects = count(array_unique(array_merge(...array_map(fn($p) => array_column($p, 'subject_id'), array_values($timetable))))); ?>
                <div class="stat-value"><?= $uniqueSubjects ?></div>
                <div class="stat-label">Unique Subjects</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-violet py-3">
            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-body">
                <?php $uniqueFaculty = count(array_unique(array_merge(...array_map(fn($p) => array_column($p, 'faculty_id'), array_values($timetable))))); ?>
                <div class="stat-value"><?= $uniqueFaculty ?></div>
                <div class="stat-label">Faculty Assigned</div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Timetable Grid -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2 text-primary"></i>Weekly Schedule</span>
        <span class="badge bg-soft-primary">Week View</span>
    </div>
    <div class="card-body p-0">
        <?php foreach ($dayMeta as $key => $meta):
            $periods = $timetable[$key] ?? [];
        ?>
        <div class="tt-day-row" style="border-left: 4px solid <?= $meta['color'] ?>;">
            <div class="tt-day-label" style="background:<?= $meta['bg'] ?>; color:<?= $meta['color'] ?>;">
                <div class="tt-day-short"><?= $meta['short'] ?></div>
                <div class="tt-day-full"><?= $meta['label'] ?></div>
                <div class="tt-day-count"><?= count($periods) ?> period<?= count($periods) !== 1 ? 's' : '' ?></div>
            </div>
            <div class="tt-periods">
                <?php if (empty($periods)): ?>
                    <div class="tt-no-class">
                        <i class="fas fa-coffee me-2 opacity-50"></i>
                        <span class="text-muted">No class scheduled</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($periods as $p): ?>
                    <div class="tt-period-card" style="border-top: 3px solid <?= $meta['color'] ?>;">
                        <div class="tt-period-time" style="color:<?= $meta['color'] ?>;">
                            <i class="fas fa-clock me-1 opacity-75"></i>
                            <?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?>
                        </div>
                        <div class="tt-period-subject" title="<?= e($p['subject_name']) ?>">
                            <?= e($p['subject_code']) ?>
                        </div>
                        <div class="tt-period-sub" title="<?= e($p['subject_name']) ?>"><?= e($p['subject_name']) ?></div>
                        <div class="tt-period-faculty">
                            <i class="fas fa-user-tie me-1 opacity-60"></i><?= e($p['faculty_name']) ?>
                        </div>
                        <?php if (!empty($p['room_number'])): ?>
                        <div class="tt-period-room">
                            <i class="fas fa-door-open me-1 opacity-60"></i>Room <?= e($p['room_number']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (hasPermission('timetable.manage')): ?>
                        <div class="tt-period-actions">
                            <a href="<?= url('timetable/'.$p['id'].'/edit') ?>" class="tt-btn-edit" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <form action="<?= url('timetable/'.$p['id'].'/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this period?')">
                                <?= csrfField() ?>
                                <button type="submit" class="tt-btn-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>

<style>
/* Timetable enterprise grid */
.tt-day-row {
    display: flex;
    align-items: stretch;
    min-height: 80px;
    border-bottom: 1px solid #f1f5f9;
}
.tt-day-row:last-child { border-bottom: none; }

.tt-day-label {
    width: 100px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: .75rem .5rem;
    text-align: center;
    border-right: 1px solid #f1f5f9;
}
.tt-day-short { font-size: .65rem; font-weight: 800; letter-spacing: .12em; }
.tt-day-full  { font-size: .75rem; font-weight: 600; margin: .15rem 0; }
.tt-day-count { font-size: .65rem; opacity: .65; font-weight: 500; }

.tt-periods {
    flex: 1;
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1rem;
    flex-wrap: wrap;
}

.tt-no-class {
    font-size: .82rem;
    padding: .5rem 0;
    display: flex;
    align-items: center;
}

.tt-period-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .65rem .85rem;
    min-width: 168px;
    max-width: 200px;
    position: relative;
    transition: box-shadow .15s, transform .15s;
}
.tt-period-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    transform: translateY(-1px);
}

.tt-period-time    { font-size: .72rem; font-weight: 700; margin-bottom: .3rem; }
.tt-period-subject { font-size: .95rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tt-period-sub     { font-size: .72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: .3rem; }
.tt-period-faculty { font-size: .72rem; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tt-period-room    { font-size: .7rem; color: #94a3b8; margin-top: .2rem; }

.tt-period-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    display: none;
    gap: 3px;
}
.tt-period-card:hover .tt-period-actions { display: flex; }

.tt-btn-edit, .tt-btn-delete {
    width: 22px; height: 22px;
    border-radius: 5px;
    border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    background: none;
}
.tt-btn-edit  { color: #4f46e5; background: #eef2ff; }
.tt-btn-edit:hover  { background: #4f46e5; color: #fff; }
.tt-btn-delete { color: #dc2626; background: #fff5f5; }
.tt-btn-delete:hover { background: #dc2626; color: #fff; }
</style>
