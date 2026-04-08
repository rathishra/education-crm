<?php
$now       = time();
$dueTs     = $assignment['due_at'] ? strtotime($assignment['due_at']) : null;
$isLate    = $dueTs && $dueTs < $now;
$hoursLeft = $dueTs ? round(($dueTs - $now) / 3600, 1) : null;
$subType   = $assignment['submission_type'];
$lastSub   = !empty($submissions) ? $submissions[0] : null;
$isGraded  = ($lastSub['status'] ?? '') === 'graded';
$maxScore  = (float)$assignment['max_score'];
$passScore = (float)$assignment['pass_score'];
?>
<style>
.asn-viewer-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; margin-bottom:1.25rem; }
.asn-viewer-card .avc-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.9rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.asn-viewer-card .avc-body { padding:1.25rem; }
.drop-zone {
    border:2px dashed #c7d2fe; border-radius:12px; padding:2.5rem; text-align:center;
    background:#f8f7ff; color:#a5b4fc; transition:all .15s; cursor:pointer;
}
.drop-zone.dragover { border-color:#6366f1; background:#ede9fe; color:#4f46e5; }
.submission-history { border:1px solid #e8e3ff; border-radius:10px; overflow:hidden; }
.sub-hist-row { display:flex; align-items:center; gap:1rem; padding:.75rem 1rem; border-bottom:1px solid #f1f5f9; font-size:.83rem; }
.sub-hist-row:last-child { border-bottom:none; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/assignments') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a"><?= e($assignment['title']) ?></h4>
        <div class="text-muted small"><?= e($assignment['course_title']) ?></div>
    </div>
</div>

<!-- Due date banner -->
<?php if ($dueTs): ?>
<div class="alert border-0 d-flex align-items-center gap-3 mb-3" style="border-radius:12px;
    background:<?= $isLate ? '#fee2e2' : ($hoursLeft < 24 ? '#fef3c7' : '#d1fae5') ?>;
    color:<?= $isLate ? '#dc2626' : ($hoursLeft < 24 ? '#92400e' : '#065f46') ?>">
    <i class="fas fa-<?= $isLate ? 'exclamation-circle' : 'clock' ?> fs-5"></i>
    <div>
        <?php if ($isLate): ?>
        <strong>Deadline passed</strong> — <?= date('d M Y, H:i', $dueTs) ?>
        <?= $assignment['allow_late'] ? '<span class="ms-2 badge bg-warning text-dark">Late submission accepted</span>' : '' ?>
        <?php else: ?>
        <strong>Due <?= date('d M Y, H:i', $dueTs) ?></strong>
        <?= $hoursLeft < 24 ? ' — <strong>'.round($hoursLeft).' hours left!</strong>' : '' ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-7">

        <!-- Instructions -->
        <div class="asn-viewer-card">
            <div class="avc-header"><i class="fas fa-file-alt me-2"></i>Instructions</div>
            <div class="avc-body">
                <div style="line-height:1.75;color:#334155;font-size:.9rem"><?= nl2br(e($assignment['instructions'])) ?></div>
                <?php
                $rubric = !empty($assignment['rubric']) ? (is_string($assignment['rubric']) ? json_decode($assignment['rubric'], true) : $assignment['rubric']) : [];
                if (!empty($rubric)): ?>
                <hr>
                <div class="fw-semibold mb-2 small">Grading Rubric</div>
                <table class="table table-sm" style="font-size:.8rem">
                    <thead><tr><th>Criterion</th><th class="text-end">Points</th></tr></thead>
                    <tbody>
                    <?php foreach ($rubric as $row): ?>
                    <tr><td><?= e($row['criterion'] ?? '') ?></td><td class="text-end"><?= e($row['points'] ?? 0) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold"><td>Total</td><td class="text-end"><?= $maxScore ?></td></tr></tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submission form -->
        <?php if ($canSubmit): ?>
        <div class="asn-viewer-card">
            <div class="avc-header"><i class="fas fa-upload me-2"></i>Your Submission <?= $attemptCount > 0 ? '(Attempt '.($attemptCount+1).')' : '' ?></div>
            <div class="avc-body">
                <form method="POST" action="<?= url('elms/assignments/'.$assignment['id'].'/submit') ?>" enctype="multipart/form-data" id="submitForm">
                    <?= csrfField() ?>

                    <?php if ($subType === 'any'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Submission Type</label>
                        <div class="d-flex gap-2 flex-wrap" id="subTypePills">
                            <?php foreach (['text'=>'Text Entry','file'=>'File Upload','url'=>'URL/Link'] as $v=>$l): ?>
                            <label class="d-flex align-items-center gap-2 p-2 px-3 border rounded-3 cursor-pointer" style="cursor:pointer;border-color:#e8e3ff!important" id="pill-<?= $v ?>">
                                <input type="radio" name="submission_type" value="<?= $v ?>" <?= $v==='text'?'checked':'' ?> class="sub-type-radio">
                                <span style="font-size:.83rem;font-weight:600"><?= $l ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="submission_type" value="<?= e($subType) ?>">
                    <?php endif; ?>

                    <!-- Text panel -->
                    <div id="panel-text" class="<?= in_array($subType, ['any','text']) ? '' : 'd-none' ?> mb-3">
                        <label class="form-label fw-semibold small">Your Answer</label>
                        <textarea name="text_content" class="form-control" rows="8" placeholder="Type your answer here…"></textarea>
                    </div>

                    <!-- File panel -->
                    <div id="panel-file" class="<?= in_array($subType, ['file']) ? '' : 'd-none' ?> mb-3">
                        <label class="form-label fw-semibold small">Upload File</label>
                        <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt d-block fs-2 mb-2"></i>
                            <div class="fw-semibold" style="font-size:.88rem">Click to browse or drag & drop</div>
                            <div style="font-size:.75rem;margin-top:.3rem">
                                <?= $assignment['allowed_file_types'] ? 'Accepted: '.e($assignment['allowed_file_types']) : 'Any file type' ?>
                                &bull; Max <?= $assignment['max_file_size_mb'] ?>MB
                            </div>
                        </div>
                        <input type="file" name="submission_file" id="fileInput" class="d-none"
                               <?= $assignment['allowed_file_types'] ? 'accept=".' . str_replace(',',',.', trim($assignment['allowed_file_types'])) . '"' : '' ?>>
                        <div id="fileName" class="mt-2 text-muted small"></div>
                    </div>

                    <!-- URL panel -->
                    <div id="panel-url" class="d-none mb-3">
                        <label class="form-label fw-semibold small">Submission URL</label>
                        <input type="url" name="url_content" class="form-control" placeholder="https://…">
                        <div class="form-text">Paste a link to Google Drive, GitHub, YouTube, or any relevant URL.</div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="border-radius:9px" id="btnSubmit">
                        <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                    </button>
                    <?php if ($isLate && $assignment['allow_late'] && $assignment['late_penalty_pct'] > 0): ?>
                    <div class="alert alert-warning mt-2 py-2 px-3 border-0" style="border-radius:8px;font-size:.8rem">
                        <i class="fas fa-exclamation-triangle me-1"></i>Late penalty of <?= $assignment['late_penalty_pct'] ?>% will be applied to your score.
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php elseif ($isOverdue): ?>
        <div class="alert alert-danger border-0" style="border-radius:12px">
            <i class="fas fa-lock me-2"></i><strong>Submission closed.</strong> The deadline has passed and late submissions are not accepted.
        </div>
        <?php elseif ($attemptCount > 0 && (int)$assignment['attempts_allowed'] > 0 && $attemptCount >= (int)$assignment['attempts_allowed']): ?>
        <div class="alert alert-info border-0" style="border-radius:12px">
            <i class="fas fa-info-circle me-2"></i>You have used all <?= $assignment['attempts_allowed'] ?> allowed attempt(s).
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: submission history -->
    <div class="col-12 col-lg-5">
        <div class="asn-viewer-card">
            <div class="avc-header"><i class="fas fa-history me-2"></i>Submission History</div>
            <div>
            <?php if (empty($submissions)): ?>
            <div class="text-center py-4 text-muted small"><i class="fas fa-inbox d-block fs-2 mb-2 opacity-25"></i>No submissions yet.</div>
            <?php else: ?>
            <?php foreach ($submissions as $sub): ?>
            <div class="sub-hist-row">
                <div style="flex:1">
                    <div class="fw-semibold" style="font-size:.83rem">Attempt <?= $sub['attempt'] ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= date('d M Y, H:i', strtotime($sub['submitted_at'])) ?></div>
                    <?php if ($sub['is_late']): ?><span class="badge" style="background:#fee2e2;color:#dc2626;font-size:.62rem">Late</span><?php endif; ?>
                </div>
                <div class="text-end">
                    <?php if ($sub['status'] === 'graded'): ?>
                    <div class="fw-bold" style="font-size:1.1rem;color:<?= $sub['score'] >= $passScore ? '#059669' : '#dc2626' ?>">
                        <?= number_format($sub['score'], 1) ?><span class="text-muted fw-normal" style="font-size:.75rem">/<?= $maxScore ?></span>
                    </div>
                    <div class="<?= $sub['score'] >= $passScore ? 'text-success' : 'text-danger' ?>" style="font-size:.72rem;font-weight:700">
                        <?= $sub['score'] >= $passScore ? 'PASSED' : 'FAILED' ?>
                    </div>
                    <?php else: ?>
                    <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.7rem;border-radius:7px"><?= ucfirst($sub['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($sub['feedback']) && $sub['status'] === 'graded'): ?>
            <div class="px-3 py-2 border-top" style="background:#fafaf9;font-size:.8rem">
                <div class="fw-semibold mb-1 text-muted small"><i class="fas fa-comment me-1"></i>Instructor Feedback</div>
                <div style="color:#334155;line-height:1.6"><?= nl2br(e($sub['feedback'])) ?></div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>

        <!-- Assignment meta -->
        <div class="asn-viewer-card">
            <div class="avc-header"><i class="fas fa-info-circle me-2"></i>Details</div>
            <div class="avc-body p-0">
                <?php foreach ([
                    ['Max Score',  $maxScore . ' pts',  'fa-star'],
                    ['Pass Score', $passScore . ' pts', 'fa-check-circle'],
                    ['Attempts',   $assignment['attempts_allowed'] ?: 'Unlimited', 'fa-redo'],
                    ['Submission', ucfirst($subType), 'fa-upload'],
                ] as [$lbl,$val,$ico]): ?>
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="font-size:.82rem">
                    <i class="fas <?= $ico ?> text-primary" style="width:14px;text-align:center"></i>
                    <span class="text-muted" style="width:90px;flex-shrink:0"><?= $lbl ?></span>
                    <span class="fw-semibold"><?= e($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Submission type switcher
document.querySelectorAll('.sub-type-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        ['text','file','url'].forEach(t => {
            document.getElementById('panel-' + t)?.classList.add('d-none');
            document.getElementById('pill-' + t)?.style && (document.getElementById('pill-' + t).style.borderColor = '#e8e3ff');
        });
        document.getElementById('panel-' + this.value)?.classList.remove('d-none');
        document.getElementById('pill-' + this.value).style.borderColor = '#6366f1';
    });
    if (radio.checked) {
        document.getElementById('pill-' + radio.value)?.style && (document.getElementById('pill-' + radio.value).style.borderColor = '#6366f1');
    }
});

// File drag & drop
const dz = document.getElementById('dropZone');
const fi = document.getElementById('fileInput');
dz?.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
dz?.addEventListener('dragleave', () => dz.classList.remove('dragover'));
dz?.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('dragover');
    if (e.dataTransfer.files[0]) {
        const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]);
        fi.files = dt.files;
        document.getElementById('fileName').textContent = '📎 ' + e.dataTransfer.files[0].name;
    }
});
fi?.addEventListener('change', () => {
    document.getElementById('fileName').textContent = fi.files[0] ? '📎 ' + fi.files[0].name : '';
});

// Submit guard
document.getElementById('submitForm')?.addEventListener('submit', function () {
    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
});
</script>
