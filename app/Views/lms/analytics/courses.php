<style>
.course-ana-row { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:.85rem 1rem; margin-bottom:.4rem; transition:box-shadow .15s; }
.course-ana-row:hover { box-shadow:0 3px 14px rgba(99,102,241,.1); }
.mini-bar { height:6px; border-radius:10px; background:#f1f5f9; overflow:hidden; }
.mini-fill { height:100%; border-radius:10px; }
</style>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/analytics') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-book-open me-2 text-primary"></i>Course Analytics</h4>
</div>

<?php if (empty($courses)): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-book-open" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No courses found</p>
</div>
<?php else: ?>

<!-- Summary stats -->
<?php
$totalEnroll = array_sum(array_column($courses,'enrollments'));
$totalComp   = array_sum(array_column($courses,'completions'));
$avgProg     = count($courses) ? round(array_sum(array_column($courses,'avg_progress')) / count($courses), 1) : 0;
?>
<div class="row g-2 mb-3">
    <?php foreach ([['Courses', count($courses), '#4f46e5'],['Total Enrollments',$totalEnroll,'#0284c7'],['Completions',$totalComp,'#16a34a'],['Avg Progress',$avgProg.'%','#d97706']] as [$l,$v,$c]): ?>
    <div class="col-6 col-md-3">
        <div class="bg-white rounded-3 border p-2 text-center" style="border-color:#e8e3ff!important">
            <div style="font-size:1.4rem;font-weight:900;color:<?= $c ?>"><?= $v ?></div>
            <div class="text-muted" style="font-size:.7rem"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php foreach ($courses as $c): ?>
<?php
$enrollments = (int)$c['enrollments'];
$completions = (int)$c['completions'];
$compRate    = $enrollments > 0 ? round($completions / $enrollments * 100) : 0;
$avgProg     = (float)($c['avg_progress'] ?? 0);
?>
<div class="course-ana-row">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div style="flex:1;min-width:180px">
            <a href="<?= url('elms/analytics/course/'.$c['id']) ?>" class="fw-bold text-decoration-none" style="color:#0f172a;font-size:.9rem"><?= e($c['title']) ?></a>
            <div class="text-muted small mt-1">
                <span class="badge" style="background:<?= $c['status']==='published'?'#d1fae5':'#f1f5f9' ?>;color:<?= $c['status']==='published'?'#065f46':'#64748b' ?>;border-radius:6px;font-size:.65rem"><?= ucfirst($c['status']) ?></span>
                &middot; <?= $c['lessons'] ?> lessons &middot; <?= $c['assignments'] ?> assignments &middot; <?= $c['quizzes'] ?> quizzes
            </div>
        </div>

        <div style="min-width:120px">
            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;color:#64748b">
                <span>Avg Progress</span><span class="fw-bold" style="color:#6366f1"><?= $avgProg ?>%</span>
            </div>
            <div class="mini-bar"><div class="mini-fill" style="width:<?= $avgProg ?>%;background:#6366f1"></div></div>
        </div>

        <div style="min-width:120px">
            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;color:#64748b">
                <span>Completion</span><span class="fw-bold" style="color:#22c55e"><?= $compRate ?>%</span>
            </div>
            <div class="mini-bar"><div class="mini-fill" style="width:<?= $compRate ?>%;background:#22c55e"></div></div>
        </div>

        <div class="text-center flex-shrink-0" style="min-width:70px">
            <div class="fw-bold" style="font-size:1.1rem;color:#0284c7"><?= $enrollments ?></div>
            <div class="text-muted" style="font-size:.68rem">Enrolled</div>
        </div>

        <a href="<?= url('elms/analytics/course/'.$c['id']) ?>" class="btn btn-sm btn-outline-primary flex-shrink-0" style="border-radius:8px"><i class="fas fa-chart-bar me-1"></i>View</a>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
