<?php
$dayLabels = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat'];
$todayDay  = strtolower(date('l'));
$subColors = ['#3b82f6','#059669','#7c3aed','#dc2626','#d97706','#0891b2','#db2777','#65a30d'];
$subColorMap = [];
$colorIdx = 0;
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-clock me-2 text-success"></i>Weekly Timetable</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Timetable</div>
    </div>
    <div class="text-muted small"><?= date('l, d F Y') ?></div>
</div>

<?php if (!$sectionId): ?>
<div class="portal-card p-5 text-center">
    <i class="fas fa-calendar-times d-block fs-1 mb-3 text-muted opacity-50"></i>
    <div class="fw-semibold mb-1">No Section Assigned</div>
    <div class="text-muted small">Please contact the administration to be assigned to a section.</div>
</div>
<?php elseif (empty($periods)): ?>
<div class="portal-card p-5 text-center">
    <i class="fas fa-calendar d-block fs-1 mb-3 text-muted opacity-50"></i>
    <div class="fw-semibold mb-1">Timetable Not Available</div>
    <div class="text-muted small">The timetable for your section has not been published yet.</div>
</div>
<?php else: ?>

<!-- Desktop Timetable Grid -->
<div class="portal-card d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="min-width:900px;font-size:0.8rem">
            <thead>
                <tr style="background:#f0fdf4">
                    <th style="width:90px;background:#f0fdf4;border-color:#d1fae5">Period</th>
                    <?php foreach ($days as $day): ?>
                    <th class="text-center <?= $day === $todayDay ? 'bg-success text-white' : '' ?>" style="<?= $day !== $todayDay ? 'background:#f0fdf4;' : '' ?>border-color:#d1fae5">
                        <?= $dayLabels[$day] ?>
                        <?php if ($day === $todayDay): ?><div style="font-size:0.65rem;opacity:0.8">Today</div><?php endif; ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($periods as $period):
                    $isBreak = (bool)($period['is_break'] ?? false);
                ?>
                <tr <?= $isBreak ? 'style="background:#fafafa"' : '' ?>>
                    <td class="text-center fw-semibold" style="border-color:#e7f5ef;background:#f0fdf4">
                        <div style="font-size:0.75rem;color:#059669"><?= e($period['period_name'] ?? '') ?></div>
                        <div style="font-size:0.68rem;color:#94a3b8">
                            <?= !empty($period['start_time']) ? date('H:i', strtotime($period['start_time'])) : '' ?>
                            <?= !empty($period['end_time']) ? ' – ' . date('H:i', strtotime($period['end_time'])) : '' ?>
                        </div>
                    </td>
                    <?php foreach ($days as $day):
                        $slot = $timetable[$day][$period['id']] ?? null;
                    ?>
                    <td class="text-center p-1" style="border-color:#e7f5ef;vertical-align:middle">
                        <?php if ($isBreak): ?>
                        <div class="text-muted" style="font-size:0.72rem;font-style:italic">Break</div>
                        <?php elseif ($slot): ?>
                        <?php
                        $subName = $slot['subject_name'];
                        if (!isset($subColorMap[$subName])) {
                            $subColorMap[$subName] = $subColors[$colorIdx % count($subColors)];
                            $colorIdx++;
                        }
                        $color = $subColorMap[$subName];
                        ?>
                        <div class="rounded-2 py-1 px-2" style="background:<?= $color ?>18;border-left:3px solid <?= $color ?>">
                            <div class="fw-semibold" style="font-size:0.78rem;color:<?= $color ?>"><?= e($subName) ?></div>
                            <?php if (!empty($slot['subject_code'])): ?>
                            <div style="font-size:0.65rem;color:#94a3b8"><?= e($slot['subject_code']) ?></div>
                            <?php endif; ?>
                            <div style="font-size:0.68rem;color:#64748b"><?= e($slot['faculty_name'] ?? '') ?></div>
                        </div>
                        <?php else: ?>
                        <div style="color:#e2e8f0;font-size:0.75rem">—</div>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Timetable (day-by-day) -->
<div class="d-lg-none">
    <?php foreach ($days as $day):
        $isToday = $day === $todayDay;
    ?>
    <div class="portal-card mb-2">
        <div class="card-header px-3 py-2" style="background:<?= $isToday ? 'linear-gradient(135deg,#059669,#10b981)' : '#f0fdf4' ?>">
            <div class="fw-bold" style="color:<?= $isToday ? '#fff' : '#065f46' ?>;font-size:0.9rem">
                <?= ucfirst($day) ?><?= $isToday ? ' <span style="font-size:0.7rem;opacity:0.8">(Today)</span>' : '' ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php
            $dayHasSlots = false;
            foreach ($periods as $period):
                $slot = $timetable[$day][$period['id']] ?? null;
                $isBreak = (bool)($period['is_break'] ?? false);
                if (!$slot && !$isBreak) continue;
                $dayHasSlots = true;
            ?>
            <div class="d-flex align-items-start gap-2 p-3 border-bottom">
                <div class="text-center flex-shrink-0" style="min-width:48px">
                    <div style="font-size:0.72rem;color:#059669;font-weight:600"><?= e($period['period_name'] ?? '') ?></div>
                    <div style="font-size:0.65rem;color:#94a3b8"><?= !empty($period['start_time']) ? date('H:i', strtotime($period['start_time'])) : '' ?></div>
                </div>
                <div>
                    <?php if ($isBreak): ?>
                    <div class="text-muted small" style="font-style:italic">Break</div>
                    <?php elseif ($slot): ?>
                    <div class="fw-semibold" style="font-size:0.875rem"><?= e($slot['subject_name']) ?></div>
                    <div class="text-muted" style="font-size:0.78rem"><?= e($slot['faculty_name'] ?? '') ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$dayHasSlots): ?>
            <div class="p-3 text-muted small text-center">No classes</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Legend -->
<div class="portal-card mt-3 p-3">
    <div class="fw-semibold mb-2" style="font-size:0.82rem;color:#374151">Subjects</div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($subColorMap as $subj => $color): ?>
        <span class="badge px-2 py-1" style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40;font-size:0.75rem">
            <?= e($subj) ?>
        </span>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>
