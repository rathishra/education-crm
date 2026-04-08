<?php
$maxScore = (float)$assignment['max_score'];
$passScore = (float)$assignment['pass_score'];
?>
<style>
.grade-panel { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; }
.grade-panel .gp-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.sub-row { border-bottom:1px solid #f1f5f9; transition:background .12s; }
.sub-row:hover { background:#fafaf9; }
.sub-row:last-child { border-bottom:none; }
.score-input { width:80px; font-size:.83rem; border-radius:7px; }
.asn-stat { background:#f8f7ff; border-radius:10px; padding:.85rem 1rem; text-align:center; border:1px solid #e8e3ff; }
.asn-stat .val { font-size:1.5rem; font-weight:900; color:#0f172a; line-height:1.1; }
.asn-stat .lbl { font-size:.7rem; color:#64748b; margin-top:.15rem; }
</style>

<div class="d-flex align-items-start justify-content-between mb-3 gap-2 flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('elms/assignments') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold mb-0" style="color:#0f172a"><?= e($assignment['title']) ?></h4>
            <div class="text-muted small"><?= e($assignment['course_title']) ?> &bull; Max: <?= $maxScore ?> pts &bull; Pass: <?= $passScore ?> pts</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('elms/assignments/'.$assignment['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary" style="border-radius:9px"><i class="fas fa-edit me-1"></i>Edit</a>
        <form method="POST" action="<?= url('elms/assignments/'.$assignment['id'].'/delete') ?>" onsubmit="return confirm('Delete this assignment?')">
            <?= csrfField() ?>
            <button class="btn btn-sm btn-outline-danger" style="border-radius:9px"><i class="fas fa-trash"></i></button>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="asn-stat"><div class="val"><?= $stats['enrolled'] ?></div><div class="lbl">Enrolled</div></div></div>
    <div class="col-6 col-md-3"><div class="asn-stat"><div class="val"><?= $stats['submitted'] ?></div><div class="lbl">Submitted</div></div></div>
    <div class="col-6 col-md-3"><div class="asn-stat"><div class="val"><?= $stats['graded'] ?></div><div class="lbl">Graded</div></div></div>
    <div class="col-6 col-md-3"><div class="asn-stat"><div class="val"><?= $stats['avg_score'] ?></div><div class="lbl">Avg Score</div></div></div>
</div>

<div class="row g-3">
    <!-- Submissions table -->
    <div class="col-12 col-lg-8">
        <div class="grade-panel">
            <div class="gp-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-paper-plane me-2"></i>Submissions (<?= count($submissions) ?>)</span>
                <?php if (count($submissions)): ?>
                <div class="d-flex gap-1">
                    <input type="text" id="subSearch" class="form-control form-control-sm" placeholder="Search learner…" style="width:160px;border-radius:7px">
                </div>
                <?php endif; ?>
            </div>
            <?php if (empty($submissions)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox d-block fs-2 mb-2 opacity-25"></i>
                <small>No submissions yet.</small>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.83rem">
                    <thead style="background:#f8f7ff">
                        <tr>
                            <th class="ps-3">Learner</th>
                            <th>Submitted</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="width:140px">Score / <?= $maxScore ?></th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="submissionsBody">
                    <?php foreach ($submissions as $sub): ?>
                    <?php
                    $initials = strtoupper(substr($sub['learner_name'], 0, 1));
                    $isGraded = $sub['status'] === 'graded';
                    $isLate   = $sub['is_late'];
                    $pct      = $sub['score'] !== null ? round($sub['score'] / $maxScore * 100) : null;
                    ?>
                    <tr class="sub-row" data-name="<?= strtolower(e($sub['learner_name'])) ?>">
                        <td class="ps-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:28px;height:28px;border-radius:50%;background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0"><?= $initials ?></div>
                                <div>
                                    <div class="fw-semibold"><?= e($sub['learner_name']) ?></div>
                                    <div class="text-muted" style="font-size:.7rem"><?= e($sub['learner_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2">
                            <div><?= date('d M y', strtotime($sub['submitted_at'])) ?></div>
                            <?php if ($isLate): ?><span class="badge" style="background:#fee2e2;color:#dc2626;font-size:.62rem">Late</span><?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if ($sub['submission_type'] === 'file' && $sub['file_path']): ?>
                            <a href="<?= url('elms/assignments/'.$assignment['id'].'/submissions/'.$sub['id'].'/download') ?>" class="btn btn-xs btn-outline-secondary" style="font-size:.72rem;padding:2px 8px;border-radius:6px">
                                <i class="fas fa-download me-1"></i><?= e(mb_strimwidth($sub['file_original'] ?? 'file', 0, 20, '…')) ?>
                            </a>
                            <?php elseif ($sub['submission_type'] === 'url'): ?>
                            <a href="<?= e($sub['url_content']) ?>" target="_blank" class="text-primary" style="font-size:.75rem"><i class="fas fa-external-link-alt me-1"></i>Link</a>
                            <?php else: ?>
                            <button class="btn btn-xs btn-outline-secondary btn-view-text" data-id="<?= $sub['id'] ?>" data-content="<?= e(htmlspecialchars($sub['text_content'] ?? '')) ?>" style="font-size:.72rem;padding:2px 8px;border-radius:6px">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if ($isGraded): ?>
                            <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.68rem">Graded</span>
                            <?php elseif ($sub['status'] === 'late'): ?>
                            <span class="badge" style="background:#fee2e2;color:#dc2626;font-size:.68rem">Late</span>
                            <?php else: ?>
                            <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.68rem">Submitted</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <div class="d-flex align-items-center gap-1">
                                <input type="number" class="form-control score-input score-field"
                                       data-sub-id="<?= $sub['id'] ?>"
                                       min="0" max="<?= $maxScore ?>" step="0.5"
                                       value="<?= $sub['score'] !== null ? $sub['score'] : '' ?>"
                                       placeholder="—"
                                       style="<?= $pct !== null && $pct >= ($passScore/$maxScore*100) ? 'border-color:#059669' : ($pct !== null ? 'border-color:#dc2626' : '') ?>">
                                <button class="btn btn-xs btn-success btn-grade" data-sub-id="<?= $sub['id'] ?>" style="padding:3px 7px;border-radius:6px" title="Save grade">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-2">
                            <button class="btn btn-xs btn-outline-secondary btn-feedback" data-sub-id="<?= $sub['id'] ?>"
                                    data-feedback="<?= e($sub['feedback'] ?? '') ?>"
                                    style="padding:3px 7px;border-radius:6px" title="Add feedback">
                                <i class="fas fa-comment<?= $sub['feedback'] ? '-dots' : '' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assignment details sidebar -->
    <div class="col-12 col-lg-4">
        <div class="grade-panel">
            <div class="gp-header"><i class="fas fa-info-circle me-2"></i>Assignment Info</div>
            <div class="p-3" style="font-size:.83rem">
                <div class="mb-2"><strong>Due:</strong> <?= $assignment['due_at'] ? date('d M Y, H:i', strtotime($assignment['due_at'])) : 'No deadline' ?></div>
                <div class="mb-2"><strong>Late:</strong> <?= $assignment['allow_late'] ? 'Allowed' . ($assignment['late_penalty_pct'] ? ' (−'.$assignment['late_penalty_pct'].'%)' : '') : 'Not allowed' ?></div>
                <div class="mb-2"><strong>Attempts:</strong> <?= $assignment['attempts_allowed'] ?: 'Unlimited' ?></div>
                <div class="mb-3"><strong>Submission:</strong> <?= ucfirst($assignment['submission_type']) ?></div>
                <hr>
                <div class="fw-semibold mb-2">Instructions</div>
                <div class="text-muted" style="line-height:1.6;max-height:200px;overflow-y:auto"><?= nl2br(e($assignment['instructions'])) ?></div>

                <?php
                $rubric = !empty($assignment['rubric']) ? (is_string($assignment['rubric']) ? json_decode($assignment['rubric'], true) : $assignment['rubric']) : [];
                if (!empty($rubric)): ?>
                <hr>
                <div class="fw-semibold mb-2">Rubric</div>
                <?php foreach ($rubric as $row): ?>
                <div class="d-flex justify-content-between mb-1" style="font-size:.8rem">
                    <span><?= e($row['criterion'] ?? '') ?></span>
                    <span class="badge bg-light text-muted border"><?= e($row['points'] ?? 0) ?> pts</span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Text submission viewer modal -->
<div class="modal fade" id="textModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:14px">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Submission Text</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="textModalContent" class="p-3 bg-light rounded" style="white-space:pre-wrap;font-size:.85rem;max-height:400px;overflow-y:auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- Feedback modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Add Feedback</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="feedbackText" class="form-control" rows="5" placeholder="Write feedback for the learner…"></textarea>
                <input type="hidden" id="feedbackSubId">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:8px">Cancel</button>
                <button class="btn btn-primary" id="btnSaveFeedback" style="border-radius:8px"><i class="fas fa-save me-1"></i>Save Feedback</button>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const ASN_ID = <?= $assignment['id'] ?>;

// Search submissions
document.getElementById('subSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#submissionsBody tr').forEach(tr => {
        tr.style.display = tr.dataset.name?.includes(q) ? '' : 'none';
    });
});

// Grade
document.querySelectorAll('.btn-grade').forEach(btn => {
    btn.addEventListener('click', function () {
        const subId    = this.dataset.subId;
        const scoreEl  = document.querySelector(`.score-field[data-sub-id="${subId}"]`);
        const feedback = document.querySelector(`[data-sub-id="${subId}"].btn-feedback`)?.dataset.feedback || '';
        const score    = scoreEl?.value;

        fetch(`<?= url('elms/assignments') ?>/${ASN_ID}/submissions/${subId}/grade`, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({ score, feedback })
        })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'ok') {
                scoreEl.style.borderColor = parseFloat(d.score) >= <?= $passScore ?> ? '#059669' : '#dc2626';
                btn.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => btn.innerHTML = '<i class="fas fa-check"></i>', 1500);
            }
        });
    });
});

// View text submission
const textModal = new bootstrap.Modal(document.getElementById('textModal'));
document.querySelectorAll('.btn-view-text').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('textModalContent').textContent = this.dataset.content;
        textModal.show();
    });
});

// Feedback
const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
document.querySelectorAll('.btn-feedback').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('feedbackSubId').value = this.dataset.subId;
        document.getElementById('feedbackText').value  = this.dataset.feedback || '';
        feedbackModal.show();
    });
});
document.getElementById('btnSaveFeedback')?.addEventListener('click', function () {
    const subId    = document.getElementById('feedbackSubId').value;
    const feedback = document.getElementById('feedbackText').value;
    const scoreEl  = document.querySelector(`.score-field[data-sub-id="${subId}"]`);

    fetch(`<?= url('elms/assignments') ?>/${ASN_ID}/submissions/${subId}/grade`, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ score: scoreEl?.value || null, feedback })
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'ok') {
            const btn = document.querySelector(`.btn-feedback[data-sub-id="${subId}"]`);
            if (btn) { btn.dataset.feedback = feedback; btn.innerHTML = '<i class="fas fa-comment-dots"></i>'; }
            feedbackModal.hide();
        }
    });
});
</script>
