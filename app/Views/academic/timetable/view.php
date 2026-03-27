<?php
$pageTitle = 'Timetable: ' . e($section['program_name']) . ' — Section ' . e($section['section_name']);
$totalSlots = 0;
foreach($timetable as $daySlots) $totalSlots += count($daySlots);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/timetable') ?>">Scheduling</a></li>
                <li class="breadcrumb-item active">View Timetable</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1">
            <?= e($section['program_name']) ?>
            <span class="badge bg-secondary"><?= e($section['batch_term']) ?></span>
            <span class="badge bg-dark">SECTION <?= e($section['section_name']) ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <i class="fas fa-calendar-week me-1"></i>Weekly Schedule
            · <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $totalSlots ?> class slots</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-light border shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Print
        </button>
        <a href="<?= url('academic/timetable/generator?section_id=' . $section['id']) ?>" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-edit me-1"></i>Edit Timetable
        </a>
        <a href="<?= url('academic/timetable') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Subject Legend -->
<?php if(!empty($subjectColors)): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small fw-bold me-1">Subjects:</span>
            <?php foreach($subjectColors as $code => $color): ?>
            <span class="badge px-2 py-1" style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>60;font-size:.75rem">
                <i class="fas fa-circle me-1" style="font-size:.5rem"></i><?= e($code) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Weekly Grid -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <?php if(empty($periods)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25 d-block"></i>
            No timetable periods configured yet.
            <div class="mt-2"><a href="<?= url('academic/timetable/generator?section_id=' . $section['id']) ?>" class="btn btn-sm btn-primary">Configure Timetable</a></div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center" style="min-width:900px;table-layout:fixed">
                <thead>
                    <tr class="table-dark">
                        <th style="width:90px" class="py-3">Day</th>
                        <?php foreach($periods as $p): ?>
                        <th class="<?= $p['is_break'] ? 'bg-secondary' : '' ?>" style="width:<?= $p['is_break'] ? '70px' : '170px' ?>">
                            <div class="fw-bold small"><?= e($p['period_name']) ?></div>
                            <div class="opacity-75" style="font-size:.65rem"><?= substr($p['start_time'],0,5) ?>–<?= substr($p['end_time'],0,5) ?></div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($days as $day):
                        $hasAnyClass = false;
                        foreach($periods as $p) {
                            if(!$p['is_break'] && isset($timetable[$day][$p['id']])) { $hasAnyClass = true; break; }
                        }
                    ?>
                    <tr <?= !$hasAnyClass ? 'class="opacity-50"' : '' ?>>
                        <td class="fw-bold text-uppercase text-secondary bg-light small" style="letter-spacing:.5px">
                            <?= ucfirst($day) ?>
                        </td>
                        <?php foreach($periods as $p):
                            $pid = $p['id'];
                            $slot = $timetable[$day][$pid] ?? null;
                            $color = $slot ? ($subjectColors[$slot['subject_code']] ?? '#6b7280') : '';
                        ?>
                        <?php if($p['is_break']): ?>
                        <td class="bg-light" style="vertical-align:middle">
                            <div class="text-muted" style="font-size:.65rem">
                                <i class="fas fa-mug-hot d-block mb-1 opacity-50"></i>
                                <?= e($p['break_name'] ?: 'BREAK') ?>
                            </div>
                        </td>
                        <?php elseif($slot): ?>
                        <td class="p-2" style="border-left:3px solid <?= $color ?>;vertical-align:top">
                            <div class="fw-bold" style="font-size:.8rem;color:<?= $color ?>"><?= e($slot['subject_code']) ?></div>
                            <div class="text-dark" style="font-size:.72rem;line-height:1.3"><?= e($slot['subject_name']) ?></div>
                            <div class="text-muted mt-1" style="font-size:.68rem">
                                <i class="fas fa-user me-1"></i><?= e($slot['faculty_name'] ?? '—') ?>
                            </div>
                            <div class="mt-1">
                                <span class="badge px-1" style="font-size:.6rem;background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40">
                                    <?= ucfirst($slot['entry_type'] ?? 'lecture') ?>
                                </span>
                            </div>
                        </td>
                        <?php else: ?>
                        <td class="bg-light" style="vertical-align:middle">
                            <span class="text-muted opacity-25" style="font-size:.75rem">—</span>
                        </td>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php if($totalSlots > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-4">
        <small class="text-muted"><?= $totalSlots ?> class slots configured across the week</small>
        <a href="<?= url('academic/attendance') ?>" class="btn btn-sm btn-success">
            <i class="fas fa-user-check me-1"></i>Faculty: Mark Attendance
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Slot Summary by Subject -->
<?php
$subjectSummary = [];
foreach($timetable as $daySlots) {
    foreach($daySlots as $slot) {
        $key = $slot['subject_code'];
        if(!isset($subjectSummary[$key])) {
            $subjectSummary[$key] = ['name'=>$slot['subject_name'], 'faculty'=>$slot['faculty_name'], 'count'=>0, 'color'=>$subjectColors[$key]??'#6b7280'];
        }
        $subjectSummary[$key]['count']++;
    }
}
?>
<?php if(!empty($subjectSummary)): ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-primary"></i>Subject Load Summary</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach($subjectSummary as $code => $info): ?>
            <div class="col-md-4 col-lg-3">
                <div class="p-3 rounded border" style="border-left:4px solid <?= $info['color'] ?>!important">
                    <div class="fw-bold" style="color:<?= $info['color'] ?>;font-size:.85rem"><?= e($code) ?></div>
                    <div class="text-dark small"><?= e($info['name']) ?></div>
                    <div class="text-muted small mt-1"><i class="fas fa-user me-1"></i><?= e($info['faculty'] ?? '—') ?></div>
                    <div class="mt-1"><span class="badge bg-light text-dark border"><?= $info['count'] ?> periods/week</span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    .btn, .breadcrumb, nav { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    table { font-size: 11px !important; }
}
</style>
