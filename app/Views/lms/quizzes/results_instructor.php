<style>
.stat-pill { background:#f8f7ff; border-radius:12px; border:1px solid #e8e3ff; padding:.9rem 1.25rem; text-align:center; }
.stat-pill .val { font-size:1.6rem; font-weight:900; color:#4f46e5; line-height:1; }
.stat-pill .lbl { font-size:.7rem; color:#64748b; margin-top:.2rem; }
</style>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/quizzes') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1;min-width:0">
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1.05rem"><i class="fas fa-chart-bar me-2 text-primary"></i><?= e($quiz['title']) ?></h4>
        <div class="text-muted small mt-1"><?= e($quiz['course_title']) ?> &middot; <?= count($questions) ?> questions &middot; <?= $totalPoints ?> pts</div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('elms/quizzes/'.$quiz['id'].'/builder') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-tools me-1"></i>Builder</a>
        <a href="<?= url('elms/quizzes/'.$quiz['id'].'/edit') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-edit me-1"></i>Edit</a>
    </div>
</div>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-pill">
            <div class="val"><?= $stats['attempts'] ?></div>
            <div class="lbl">Total Attempts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-pill">
            <div class="val" style="color:#22c55e"><?= $stats['passed'] ?></div>
            <div class="lbl">Passed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-pill">
            <div class="val"><?= $stats['avg_score'] ?>%</div>
            <div class="lbl">Avg Score</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-pill">
            <?php $am = floor($stats['avg_time']/60); $as = $stats['avg_time']%60; ?>
            <div class="val"><?= $am ?>m<?= $as ?>s</div>
            <div class="lbl">Avg Time</div>
        </div>
    </div>
</div>

<!-- Pass rate bar -->
<?php if ($stats['attempts'] > 0): ?>
<?php $passRate = round($stats['passed'] / $stats['attempts'] * 100); ?>
<div class="bg-white rounded-3 border p-3 mb-4" style="border-color:#e8e3ff!important">
    <div class="d-flex justify-content-between mb-1">
        <span class="small fw-semibold">Pass Rate</span>
        <span class="small fw-bold" style="color:#22c55e"><?= $passRate ?>%</span>
    </div>
    <div class="progress" style="height:10px;border-radius:10px">
        <div class="progress-bar bg-success" style="width:<?= $passRate ?>%;border-radius:10px"></div>
    </div>
</div>
<?php endif; ?>

<!-- Attempts table -->
<div class="bg-white rounded-3 border" style="border-color:#e8e3ff!important;overflow:hidden">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color:#e8e3ff!important">
        <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-list me-2 text-primary"></i>All Attempts</span>
        <?php if (!empty($attempts)): ?>
        <span class="text-muted small"><?= count($attempts) ?> submission<?= count($attempts)!=1?'s':'' ?></span>
        <?php endif; ?>
    </div>
    <?php if (empty($attempts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox" style="font-size:2.5rem;opacity:.2"></i>
        <p class="mt-2 small">No submissions yet</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.83rem">
            <thead style="background:#f8f7ff">
                <tr>
                    <th class="fw-semibold ps-3" style="color:#64748b">Learner</th>
                    <th class="fw-semibold text-center" style="color:#64748b">Attempt</th>
                    <th class="fw-semibold text-center" style="color:#64748b">Score</th>
                    <th class="fw-semibold text-center" style="color:#64748b">%</th>
                    <th class="fw-semibold text-center" style="color:#64748b">Status</th>
                    <th class="fw-semibold text-center" style="color:#64748b">Time Taken</th>
                    <th class="fw-semibold" style="color:#64748b">Submitted</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($attempts as $a): ?>
            <?php
            $pct    = round((float)$a['percentage']);
            $passed = $a['passed'];
            $tm     = (int)$a['time_taken_s'];
            ?>
            <tr>
                <td class="ps-3 py-2">
                    <div class="fw-semibold" style="color:#0f172a"><?= e($a['learner_name']) ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= e($a['learner_email']) ?></div>
                </td>
                <td class="text-center py-2">#<?= $a['attempt'] ?></td>
                <td class="text-center py-2 fw-bold"><?= number_format((float)$a['score'],1) ?>/<?= number_format((float)$a['max_score'],1) ?></td>
                <td class="text-center py-2">
                    <span class="fw-bold" style="color:<?= $pct >= $quiz['pass_percentage'] ? '#16a34a' : '#dc2626' ?>"><?= $pct ?>%</span>
                </td>
                <td class="text-center py-2">
                    <?php if ($a['status'] === 'in_progress'): ?>
                    <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:8px">In Progress</span>
                    <?php elseif ($passed): ?>
                    <span class="badge" style="background:#d1fae5;color:#065f46;border-radius:8px"><i class="fas fa-check me-1"></i>Passed</span>
                    <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;border-radius:8px"><i class="fas fa-times me-1"></i>Failed</span>
                    <?php endif; ?>
                </td>
                <td class="text-center py-2 text-muted"><?= floor($tm/60) ?>m <?= $tm%60 ?>s</td>
                <td class="py-2 text-muted"><?= $a['submitted_at'] ? date('d M Y H:i', strtotime($a['submitted_at'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Question breakdown -->
<?php if (!empty($questions)): ?>
<div class="bg-white rounded-3 border mt-4" style="border-color:#e8e3ff!important;overflow:hidden">
    <div class="p-3 border-bottom fw-bold small" style="color:#0f172a;border-color:#e8e3ff!important"><i class="fas fa-list-ol me-2 text-primary"></i>Question Summary (<?= count($questions) ?> questions)</div>
    <div class="p-3">
        <?php foreach ($questions as $i => $q): ?>
        <div class="d-flex align-items-center gap-3 py-2 <?= $i<count($questions)-1?'border-bottom':'' ?>" style="border-color:#f1f5f9!important">
            <div style="min-width:24px;font-size:.75rem;font-weight:700;color:#94a3b8;text-align:right"><?= $i+1 ?></div>
            <div style="flex:1;min-width:0">
                <div class="small fw-semibold" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($q['question']) ?></div>
                <div class="text-muted" style="font-size:.7rem"><?= ucfirst(str_replace('_',' ',$q['type'])) ?> &middot; <?= $q['points'] ?> pt<?= $q['points']!=1?'s':'' ?></div>
            </div>
            <?php if (!empty($q['options'])): ?>
            <div class="d-flex flex-wrap gap-1" style="max-width:200px">
                <?php foreach ($q['options'] as $opt): if (!$opt['is_correct']) continue; ?>
                <span class="badge" style="background:#d1fae5;color:#065f46;border-radius:6px;font-size:.68rem"><i class="fas fa-check me-1"></i><?= e(mb_strimwidth($opt['option_text'],0,25,'…')) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
