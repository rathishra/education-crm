<?php $pageTitle = 'Faculty Performance Reviews'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-star me-2 text-warning"></i>Performance Reviews</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item active">Performance</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewModal">
        <i class="fas fa-plus me-1"></i>Add Review
    </button>
</div>

<!-- ── FILTERS ────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <select name="faculty_id" class="form-select form-select-sm">
                    <option value="">All Faculty</option>
                    <?php foreach ($facultyList as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $facultyFilter == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="period" class="form-select form-select-sm">
                    <option value="">All Periods</option>
                    <?php foreach ($periods as $p): ?>
                    <option value="<?= e($p) ?>" <?= $periodFilter === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= url('faculty/performance') ?>" class="btn btn-light btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- ── REVIEWS TABLE ──────────────────────────────────────────── -->
<?php if (empty($reviews)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-star fa-3x mb-3 opacity-25"></i>
        <p class="mb-0">No performance reviews found. Click <strong>Add Review</strong> to create the first one.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Faculty</th>
                        <th class="text-center">Period</th>
                        <th class="text-center">Teaching</th>
                        <th class="text-center">Punctuality</th>
                        <th class="text-center">Research</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Student FB</th>
                        <th class="text-center">Overall</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rv):
                        $overall = (float)$rv['overall_rating'];
                        $pct     = min(100, $overall / 5 * 100);
                        $oCls    = $pct >= 80 ? 'success' : ($pct >= 60 ? 'primary' : ($pct >= 40 ? 'warning' : 'danger'));
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url("faculty/{$rv['faculty_id']}") ?>" class="fw-semibold text-dark text-decoration-none">
                                <?= e($rv['faculty_name']) ?>
                            </a>
                            <div class="text-muted small"><?= e($rv['department_name'] ?: $rv['designation'] ?: '') ?></div>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-dark border fw-semibold"><?= e($rv['review_period']) ?></span></td>
                        <td class="text-center"><?= renderStars((int)$rv['teaching_quality']) ?></td>
                        <td class="text-center"><?= renderStars((int)$rv['punctuality']) ?></td>
                        <td class="text-center"><?= renderStars((int)$rv['research_contribution']) ?></td>
                        <td class="text-center"><?= renderStars((int)$rv['admin_contribution']) ?></td>
                        <td class="text-center">
                            <?= $rv['student_feedback_score'] ? '<span class="fw-semibold">'.number_format($rv['student_feedback_score'],1).'</span><span class="text-muted">/5</span>' : '—' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $oCls ?> fs-6"><?= number_format($overall,1) ?>/5</span>
                            <div class="progress mt-1" style="height:3px">
                                <div class="progress-bar bg-<?= $oCls ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $rv['status']==='submitted'?'primary':($rv['status']==='acknowledged'?'success':'secondary') ?>">
                                <?= ucfirst($rv['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary btn-edit-review"
                                    data-id="<?= $rv['id'] ?>"
                                    data-faculty="<?= $rv['faculty_id'] ?>"
                                    data-period="<?= e($rv['review_period']) ?>"
                                    data-tq="<?= $rv['teaching_quality'] ?>"
                                    data-pu="<?= $rv['punctuality'] ?>"
                                    data-rc="<?= $rv['research_contribution'] ?>"
                                    data-ac="<?= $rv['admin_contribution'] ?>"
                                    data-fb="<?= $rv['student_feedback_score'] ?>"
                                    data-comments="<?= e($rv['comments'] ?? '') ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── ADD/EDIT REVIEW MODAL ──────────────────────────────────── -->
<div class="modal fade" id="addReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('faculty/performance/save') ?>" id="reviewForm">
                <?= csrfField() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-star me-2 text-warning"></i>
                        <span id="modalReviewTitle">Add Performance Review</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Faculty Member <span class="text-danger">*</span></label>
                            <select name="faculty_id" id="reviewFaculty" class="form-select" required>
                                <option value="">— Select Faculty —</option>
                                <?php foreach ($facultyList as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= e($f['name']) ?> <?= $f['designation'] ? '('.$f['designation'].')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Review Period <span class="text-danger">*</span></label>
                            <input type="text" name="review_period" id="reviewPeriod" class="form-control"
                                   placeholder="e.g. 2024-2025 or Sem-1 2025" required>
                        </div>
                    </div>

                    <h6 class="text-secondary border-bottom pb-2 mb-3">Rating Criteria <span class="text-muted small fw-normal">(1 = Poor, 5 = Excellent)</span></h6>

                    <div class="row g-3 mb-3">
                        <?php
                        $criteria = [
                            ['teaching_quality',     'reviewTQ', 'Teaching Quality',       'fas fa-chalkboard-teacher text-primary'],
                            ['punctuality',          'reviewPU', 'Punctuality & Regularity','fas fa-clock text-info'],
                            ['research_contribution','reviewRC', 'Research Contribution',   'fas fa-flask text-success'],
                            ['admin_contribution',   'reviewAC', 'Administrative Work',     'fas fa-tasks text-warning'],
                        ];
                        foreach ($criteria as [$name,$id,$label,$icon]):
                        ?>
                        <div class="col-md-6">
                            <label class="form-label small"><i class="<?= $icon ?> me-1"></i><?= $label ?></label>
                            <div class="d-flex gap-1 align-items-center">
                                <div class="star-rating" data-target="<?= $id ?>">
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                    <i class="far fa-star fs-4 star-btn text-warning" data-val="<?= $i ?>" style="cursor:pointer"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="<?= $name ?>" id="<?= $id ?>" value="0">
                                <span class="badge bg-light text-dark border ms-1" id="<?= $id ?>_badge">0/5</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small"><i class="fas fa-user-graduate text-danger me-1"></i>Student Feedback Score</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="student_feedback_score" id="reviewFB"
                                       class="form-control" min="0" max="5" step="0.1" placeholder="e.g. 4.2">
                                <span class="input-group-text">/ 5</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Calculated Overall</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="overallDisplay" class="form-control bg-light" readonly placeholder="—">
                                <span class="input-group-text">/ 5</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small">Comments / Remarks</label>
                        <textarea name="comments" id="reviewComments" class="form-control form-control-sm" rows="2" placeholder="Optional comments…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function renderStars(int $val, int $max = 5): string {
    $h = '';
    for ($i = 1; $i <= $max; $i++) {
        $h .= $i <= $val
            ? '<i class="fas fa-star text-warning" style="font-size:.7rem"></i>'
            : '<i class="far fa-star text-muted" style="font-size:.7rem"></i>';
    }
    return $h;
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Star rating widgets
    document.querySelectorAll('.star-rating').forEach(function (widget) {
        const targetId = widget.dataset.target;
        const input    = document.getElementById(targetId);
        const badge    = document.getElementById(targetId + '_badge');
        const stars    = widget.querySelectorAll('.star-btn');

        function highlight(val) {
            stars.forEach((s, i) => {
                s.classList.toggle('fas', i < val);
                s.classList.toggle('far', i >= val);
            });
            if (badge) badge.textContent = val + '/5';
        }

        stars.forEach(star => {
            star.addEventListener('click', () => {
                input.value = star.dataset.val;
                highlight(parseInt(star.dataset.val));
                recalcOverall();
            });
            star.addEventListener('mouseenter', () => highlight(parseInt(star.dataset.val)));
            star.addEventListener('mouseleave', () => highlight(parseInt(input.value)));
        });
    });

    function recalcOverall() {
        const tq = parseInt(document.getElementById('reviewTQ').value) || 0;
        const pu = parseInt(document.getElementById('reviewPU').value) || 0;
        const rc = parseInt(document.getElementById('reviewRC').value) || 0;
        const ac = parseInt(document.getElementById('reviewAC').value) || 0;
        const avg = ((tq + pu + rc + ac) / 4).toFixed(1);
        document.getElementById('overallDisplay').value = avg;
    }

    // ── Edit button populate
    document.querySelectorAll('.btn-edit-review').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalReviewTitle').textContent = 'Edit Performance Review';
            const sel = document.getElementById('reviewFaculty');
            sel.value = this.dataset.faculty;
            document.getElementById('reviewPeriod').value   = this.dataset.period;
            document.getElementById('reviewFB').value       = this.dataset.fb || '';
            document.getElementById('reviewComments').value = this.dataset.comments;

            function setStars(id, val) {
                document.getElementById(id).value = val;
                const w = document.querySelector('[data-target="'+id+'"]');
                if (!w) return;
                const stars = w.querySelectorAll('.star-btn');
                stars.forEach((s, i) => {
                    s.classList.toggle('fas', i < val);
                    s.classList.toggle('far', i >= val);
                });
                const badge = document.getElementById(id+'_badge');
                if (badge) badge.textContent = val+'/5';
            }
            setStars('reviewTQ', parseInt(this.dataset.tq) || 0);
            setStars('reviewPU', parseInt(this.dataset.pu) || 0);
            setStars('reviewRC', parseInt(this.dataset.rc) || 0);
            setStars('reviewAC', parseInt(this.dataset.ac) || 0);
            recalcOverall();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addReviewModal')).show();
        });
    });

    // Reset modal on close
    document.getElementById('addReviewModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('reviewForm').reset();
        document.getElementById('modalReviewTitle').textContent = 'Add Performance Review';
        document.getElementById('overallDisplay').value = '';
        document.querySelectorAll('.star-btn').forEach(s => { s.classList.remove('fas'); s.classList.add('far'); });
        document.querySelectorAll('[id$="_badge"]').forEach(b => { if (b.id.endsWith('_badge')) b.textContent = '0/5'; });
    });
});
</script>
