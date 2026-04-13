<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Timetable') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #333; background: #fff; }
        .page-header { padding: 16px 20px; border-bottom: 2px solid #0d6efd; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .page-header h1 { font-size: 16px; color: #0d6efd; font-weight: 700; }
        .page-header .meta { font-size: 10px; color: #666; text-align: right; }
        .section-block { margin: 0 20px 24px; page-break-inside: avoid; }
        .section-title { background: #0d6efd; color: #fff; padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 4px 4px 0 0; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f0f4ff; font-weight: 600; padding: 5px 6px; border: 1px solid #d0d8e8; text-align: center; }
        th.period-col { background: #e8ecf5; text-align: left; width: 100px; }
        td { border: 1px solid #dde4ef; padding: 4px 6px; vertical-align: top; min-height: 36px; }
        td.period-cell { background: #f8faff; font-weight: 600; color: #555; font-size: 9px; }
        td.break-cell { background: #fffbea; text-align: center; color: #997700; font-style: italic; padding: 4px; }
        .slot { }
        .slot .subj { font-weight: 700; color: #0d6efd; font-size: 9.5px; }
        .slot .faculty { color: #555; font-size: 9px; }
        .slot .room { color: #888; font-size: 8.5px; }
        .slot .lab-badge { display: inline-block; background: #fff3cd; color: #856404; border-radius: 2px; padding: 0 3px; font-size: 8px; font-weight: 600; }
        .empty-slot { color: #ccc; font-size: 9px; text-align: center; padding-top: 8px; }
        .footer { margin: 20px; font-size: 9px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 8px; }
        .stats-bar { display: flex; gap: 24px; padding: 8px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; margin-bottom: 12px; font-size: 10px; }
        .stat { }
        .stat strong { color: #0d6efd; }
        @media print {
            body { font-size: 10px; }
            .no-print { display: none !important; }
            .section-block { page-break-after: always; }
            .section-block:last-child { page-break-after: avoid; }
        }
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #0d6efd; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    </style>
</head>
<body>

<div class="page-header">
    <div>
        <h1><?= e($title ?? 'Timetable') ?></h1>
        <div style="font-size:10px;color:#666;margin-top:2px"><?= e($subtitle ?? '') ?></div>
    </div>
    <div class="meta">
        <div>Run: <strong><?= e($run['run_name'] ?? '') ?></strong></div>
        <div>Score: <strong><?= number_format((float)($run['score'] ?? 0), 1) ?>%</strong></div>
        <div>Generated: <?= date('d M Y, H:i') ?></div>
    </div>
</div>

<div class="stats-bar">
    <div class="stat">Sections: <strong><?= count($bySection ?? []) ?></strong></div>
    <div class="stat">Slots Assigned: <strong><?= (int)($run['assigned_count'] ?? 0) ?></strong></div>
    <div class="stat">Conflicts: <strong><?= (int)($run['conflict_count'] ?? 0) ?></strong></div>
    <div class="stat">Working Days: <strong><?= implode(', ', array_map('ucfirst', $scopeDays ?? [])) ?></strong></div>
</div>

<?php foreach ($bySection as $secId => $sec): ?>
<div class="section-block">
    <div class="section-title"><?= e($sec['name']) ?></div>
    <table>
        <thead>
            <tr>
                <th class="period-col">Period</th>
                <?php foreach ($scopeDays as $day): ?>
                    <th><?= ucfirst(substr($day, 0, 3)) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($periods as $period): ?>
                <?php if ($period['is_break'] ?? false): ?>
                    <tr>
                        <td class="break-cell" colspan="<?= count($scopeDays) + 1 ?>">
                            ☕ <?= e($period['period_name']) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td class="period-cell">
                            <?= e($period['period_name']) ?><br>
                            <span style="font-weight:400;color:#999">
                                <?= date('g:i', strtotime($period['start_time'])) ?>–<?= date('g:i A', strtotime($period['end_time'])) ?>
                            </span>
                        </td>
                        <?php foreach ($scopeDays as $day): ?>
                            <?php $slot = $sec['rows'][$day][$period['id']] ?? null; ?>
                            <td>
                                <?php if ($slot): ?>
                                    <div class="slot">
                                        <div class="subj"><?= e($slot['subject_name'] ?? ('Sub#' . $slot['subject_id'])) ?></div>
                                        <?php if (!empty($slot['faculty_name'])): ?>
                                            <div class="faculty"><?= e($slot['faculty_name']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($slot['room_name'])): ?>
                                            <div class="room"><?= e($slot['room_name']) ?></div>
                                        <?php endif; ?>
                                        <?php if (($slot['entry_type'] ?? '') === 'lab'): ?>
                                            <span class="lab-badge">LAB</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-slot">—</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<?php if (!empty($conflicts)): ?>
<div style="margin: 0 20px 20px; padding: 10px 14px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-size: 10px;">
    <strong style="color:#856404">⚠ Unresolved Conflicts (<?= count($conflicts) ?>):</strong>
    <ul style="margin:4px 0 0 16px;padding:0">
        <?php foreach ($conflicts as $c): ?>
            <li><?= e($c['section_name'] ?? 'Sec#' . $c['section_id']) ?> — <?= e($c['subject_name'] ?? 'Sub#' . $c['subject_id']) ?> (occurrence <?= (int)($c['occurrence'] ?? 1) ?>)</li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="footer">
    Generated by EduMatrix Timetable Generator &middot; <?= date('d M Y H:i') ?>
</div>

<button class="print-btn no-print" onclick="window.print()">🖨 Print</button>

</body>
</html>
