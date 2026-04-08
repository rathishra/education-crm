<?php
$isInstructor = in_array($lmsUser['role'] ?? '', ['lms_admin','instructor']);
$statusConf = [
    'draft'       => ['Draft',        '#92400e','#fef3c7'],
    'published'   => ['Published',    '#065f46','#d1fae5'],
    'archived'    => ['Archived',     '#64748b','#f1f5f9'],
    'coming_soon' => ['Coming Soon',  '#1e40af','#dbeafe'],
];
[$statusLabel,$statusColor,$statusBg] = $statusConf[$course['status']] ?? ['Unknown','#64748b','#f1f5f9'];
$totalLessons = array_sum(array_map(fn($s) => count($s['lessons']), $sections));
?>
<style>
.lms-show-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1.25rem; overflow:hidden; }
.lms-show-card .card-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; display:flex; align-items:center; justify-content:space-between; }
.lms-show-card .card-body { padding:1.25rem; }
.lms-stat-box { background:#f8f7ff; border-radius:10px; padding:.9rem 1rem; text-align:center; border:1px solid #e8e3ff; }
.lms-stat-box .val { font-size:1.6rem; font-weight:900; color:#0f172a; line-height:1.1; }
.lms-stat-box .lbl { font-size:.7rem; color:#64748b; margin-top:.15rem; }
.section-block { border:1px solid #e8e3ff; border-radius:10px; margin-bottom:.75rem; overflow:hidden; }
.section-header { background:#f8f7ff; padding:.7rem 1rem; display:flex; align-items:center; gap:.75rem; cursor:pointer; }
.section-header h6 { margin:0; font-size:.88rem; font-weight:700; color:#3730a3; flex:1; }
.section-body { padding:.5rem; }
.lesson-row { display:flex; align-items:center; gap:.6rem; padding:.5rem .6rem; border-radius:8px; font-size:.83rem; transition:background .12s; }
.lesson-row:hover { background:#f1f5f9; }
.lesson-icon { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.lesson-type-video    { background:#dbeafe; color:#1d4ed8; }
.lesson-type-document { background:#fef3c7; color:#92400e; }
.lesson-type-text     { background:#f0fdf4; color:#065f46; }
.lesson-type-quiz     { background:#ede9fe; color:#7c3aed; }
.lesson-type-assignment { background:#fce7f3; color:#9d174d; }
.lesson-type-live     { background:#fee2e2; color:#dc2626; }
</style>

<!-- PAGE HEADER -->
<div class="d-flex align-items-start justify-content-between mb-3 gap-2 flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('elms/courses') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold mb-0" style="color:#0f172a;max-width:600px"><?= e($course['title']) ?></h4>
            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                <span class="badge" style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;border-radius:8px;font-size:.7rem"><?= $statusLabel ?></span>
                <span class="text-muted small"><?= e($course['code'] ?? '') ?></span>
                <?php if (!empty($course['subject_name'])): ?>
                <span class="badge" style="background:#ede9fe;color:#6366f1;border-radius:8px;font-size:.68rem">
                    <i class="fas fa-graduation-cap me-1"></i><?= e($course['subject_code']) ?> — <?= e($course['subject_name']) ?>
                    <?php if ($course['credits']): ?> &middot; <?= $course['credits'] ?> cr<?php endif; ?>
                    <?php if ($course['subject_semester']): ?> &middot; Sem <?= $course['subject_semester'] ?><?php endif; ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($tags)): ?>
                <?php foreach ($tags as $tag): ?>
                <span class="badge bg-light text-muted border" style="font-size:.65rem;border-radius:6px"><?= e($tag) ?></span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($isInstructor): ?>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('elms/courses/'.$course['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary" style="border-radius:9px">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <button class="btn btn-sm btn-<?= $course['status']==='published'?'warning':'success' ?>" id="btnToggleStatus" style="border-radius:9px">
            <i class="fas fa-<?= $course['status']==='published'?'eye-slash':'globe' ?> me-1"></i>
            <?= $course['status']==='published'?'Unpublish':'Publish' ?>
        </button>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" style="border-radius:9px" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:10px">
                <li><a class="dropdown-item small text-danger" href="#" onclick="confirmDelete(event)"><i class="fas fa-trash-alt me-2"></i>Delete Course</a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- STATS ROW -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="lms-stat-box"><div class="val"><?= number_format($enrollStats['total'] ?? 0) ?></div><div class="lbl">Total Enrolled</div></div></div>
    <div class="col-6 col-md-3"><div class="lms-stat-box"><div class="val"><?= number_format($enrollStats['active_cnt'] ?? 0) ?></div><div class="lbl">Active Learners</div></div></div>
    <div class="col-6 col-md-3"><div class="lms-stat-box"><div class="val"><?= number_format($enrollStats['completed_cnt'] ?? 0) ?></div><div class="lbl">Completed</div></div></div>
    <div class="col-6 col-md-3"><div class="lms-stat-box"><div class="val"><?= number_format($enrollStats['avg_progress'] ?? 0, 1) ?>%</div><div class="lbl">Avg Progress</div></div></div>
</div>

<div class="row g-3">
    <!-- LEFT: Curriculum -->
    <div class="col-12 col-lg-7">
        <div class="lms-show-card">
            <div class="card-header">
                <span><i class="fas fa-list-ol me-2"></i>Curriculum <span class="badge bg-primary ms-1"><?= count($sections) ?> sections &bull; <?= $totalLessons ?> lessons</span></span>
                <?php if ($isInstructor): ?>
                <button class="btn btn-xs btn-primary" id="btnAddSection" style="font-size:.75rem;padding:4px 12px;border-radius:7px"><i class="fas fa-plus me-1"></i>Add Section</button>
                <?php endif; ?>
            </div>
            <div class="card-body p-2" id="curriculumContainer">
                <?php if (empty($sections)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-layer-group d-block fs-2 mb-2 opacity-25"></i>
                    <small>No sections yet. Add your first section above.</small>
                </div>
                <?php else: ?>
                <?php foreach ($sections as $sec): ?>
                <div class="section-block" data-sec-id="<?= $sec['id'] ?>">
                    <div class="section-header" onclick="this.closest('.section-block').querySelector('.section-body').classList.toggle('d-none')">
                        <i class="fas fa-grip-vertical text-muted" style="cursor:grab;font-size:.8rem"></i>
                        <h6><?= e($sec['title']) ?></h6>
                        <span class="text-muted" style="font-size:.72rem"><?= count($sec['lessons']) ?> lesson<?= count($sec['lessons'])!=1?'s':'' ?></span>
                        <?php if ($isInstructor): ?>
                        <button class="btn btn-xs btn-outline-secondary ms-1 btn-edit-section" data-id="<?= $sec['id'] ?>" data-title="<?= e($sec['title']) ?>" onclick="event.stopPropagation()" style="font-size:.7rem;padding:2px 8px;border-radius:6px"><i class="fas fa-pen"></i></button>
                        <button class="btn btn-xs btn-outline-danger ms-1 btn-del-section" data-id="<?= $sec['id'] ?>" onclick="event.stopPropagation()" style="font-size:.7rem;padding:2px 8px;border-radius:6px"><i class="fas fa-trash"></i></button>
                        <a href="<?= url('elms/courses/'.$course['id'].'/lessons/create?section_id='.$sec['id']) ?>" class="btn btn-xs btn-outline-primary ms-2" onclick="event.stopPropagation()" style="font-size:.7rem;padding:2px 8px;border-radius:6px"><i class="fas fa-plus me-1"></i>Lesson</a>
                        <?php endif; ?>
                    </div>
                    <div class="section-body">
                        <?php if (empty($sec['lessons'])): ?>
                        <div class="text-center py-2 text-muted" style="font-size:.78rem"><i class="fas fa-inbox me-1"></i>No lessons</div>
                        <?php else: ?>
                        <?php foreach ($sec['lessons'] as $lesson): ?>
                        <?php
                        $typeConf = [
                            'video'=>['fa-play-circle','lesson-type-video'],
                            'document'=>['fa-file-pdf','lesson-type-document'],
                            'text'=>['fa-file-alt','lesson-type-text'],
                            'quiz'=>['fa-question-circle','lesson-type-quiz'],
                            'assignment'=>['fa-tasks','lesson-type-assignment'],
                            'live'=>['fa-video','lesson-type-live'],
                        ];
                        [$icon,$cls] = $typeConf[$lesson['type']] ?? ['fa-circle','lesson-type-text'];
                        ?>
                        <div class="lesson-row">
                            <i class="fas fa-grip-vertical text-muted" style="font-size:.7rem;cursor:grab"></i>
                            <div class="lesson-icon <?= $cls ?>"><i class="fas <?= $icon ?>"></i></div>
                            <span class="flex-1" style="flex:1"><?= e($lesson['title']) ?></span>
                            <?php if ($lesson['video_duration']): ?>
                            <span class="text-muted" style="font-size:.7rem"><?= gmdate('i:s', $lesson['video_duration']) ?></span>
                            <?php endif; ?>
                            <?php if ($lesson['is_free']): ?>
                            <span class="badge bg-success-subtle text-success" style="font-size:.62rem;border-radius:5px">Free</span>
                            <?php endif; ?>
                            <?php if ($isInstructor): ?>
                            <a href="<?= url('elms/courses/'.$course['id'].'/lessons/'.$lesson['id'].'/edit') ?>" class="btn btn-xs btn-outline-secondary ms-1" style="font-size:.68rem;padding:2px 7px;border-radius:5px"><i class="fas fa-pen"></i></a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: Course info + Enrollments -->
    <div class="col-12 col-lg-5">
        <!-- Course Details -->
        <div class="lms-show-card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Course Details</div>
            <div class="card-body p-0">
                <?php
                $details = [
                    ['Instructor',  $course['instructor_name'], 'fa-chalkboard-teacher'],
                    ['Category',    $course['category_name'] ?? 'Uncategorized', 'fa-tag'],
                    ['Level',       ucwords(str_replace('_',' ',$course['level'])), 'fa-signal'],
                    ['Language',    $course['language'], 'fa-globe'],
                    ['Duration',    $course['duration_hours'] ? $course['duration_hours'].' hrs' : 'Self-paced', 'fa-clock'],
                    ['Pass Score',  $course['pass_percentage'].'%', 'fa-check-circle'],
                    ['Certificate', $course['certificate_enabled'] ? 'Yes' : 'No', 'fa-certificate'],
                    ['Self-Enroll', $course['allow_self_enroll'] ? 'Yes' : 'No', 'fa-door-open'],
                    ['Visibility',  ucfirst($course['visibility']), 'fa-eye'],
                    ['Max Students',$course['max_students'] ? number_format($course['max_students']) : 'Unlimited', 'fa-users'],
                ];
                ?>
                <?php foreach ($details as [$lbl,$val,$icon]): ?>
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="font-size:.83rem">
                    <i class="fas <?= $icon ?> text-primary" style="width:14px;text-align:center"></i>
                    <span class="text-muted" style="width:120px;flex-shrink:0"><?= $lbl ?></span>
                    <span class="fw-semibold"><?= e($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Enrollments -->
        <div class="lms-show-card">
            <div class="card-header">
                <span><i class="fas fa-users me-2"></i>Recent Enrollments</span>
                <?php if ($isInstructor): ?>
                <button class="btn btn-xs btn-outline-primary" id="btnEnrollStudents" style="font-size:.72rem;padding:3px 10px;border-radius:6px"><i class="fas fa-user-plus me-1"></i>Enroll</button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentEnrollments)): ?>
                <div class="text-center py-3 text-muted small"><i class="fas fa-users me-1"></i>No enrollments yet</div>
                <?php else: ?>
                <?php foreach ($recentEnrollments as $enr): ?>
                <?php
                $initials = strtoupper(substr($enr['learner_name'],0,1));
                $bgColors = ['#6366f1','#0891b2','#059669','#d97706','#dc2626'];
                $bg = $bgColors[crc32($enr['learner_name']) % count($bgColors)];
                ?>
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="font-size:.82rem">
                    <div style="width:28px;height:28px;border-radius:50%;background:<?= $bg ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0"><?= $initials ?></div>
                    <div style="flex:1;overflow:hidden">
                        <div class="fw-semibold text-truncate"><?= e($enr['learner_name']) ?></div>
                        <div class="text-muted" style="font-size:.7rem"><?= e($enr['email']) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold text-primary" style="font-size:.8rem"><?= $enr['progress'] ?>%</div>
                        <div class="text-muted" style="font-size:.68rem"><?= ucfirst($enr['status']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="addSectionTitle">Add Section</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="sectionTitleInput" class="form-control" placeholder="e.g. Introduction">
                <input type="hidden" id="editingSectionId" value="">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:8px">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnSaveSection" style="border-radius:8px"><i class="fas fa-check me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="<?= url('elms/courses/'.$course['id'].'/delete') ?>" class="d-none">
    <?= csrfField() ?>
</form>

<script>
const COURSE_ID  = <?= $course['id'] ?>;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const ajaxHeaders = {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': CSRF_TOKEN};

// Toggle publish status
document.getElementById('btnToggleStatus')?.addEventListener('click', function () {
    if (!confirm('Are you sure you want to change the course status?')) return;
    fetch(`<?= url('elms/courses') ?>/${COURSE_ID}/toggle-status`, {
        method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': CSRF_TOKEN}
    }).then(r => r.json()).then(d => { if (d.status==='ok') location.reload(); });
});

// Delete
function confirmDelete(e) {
    e.preventDefault();
    if (confirm('Delete this course? This cannot be undone.')) document.getElementById('deleteForm').submit();
}

// Section modal
const sectionModal = new bootstrap.Modal(document.getElementById('addSectionModal'));

document.getElementById('btnAddSection')?.addEventListener('click', () => {
    document.getElementById('addSectionTitle').textContent = 'Add Section';
    document.getElementById('sectionTitleInput').value = '';
    document.getElementById('editingSectionId').value = '';
    sectionModal.show();
});

document.querySelectorAll('.btn-edit-section').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('addSectionTitle').textContent = 'Edit Section';
        document.getElementById('sectionTitleInput').value = this.dataset.title;
        document.getElementById('editingSectionId').value = this.dataset.id;
        sectionModal.show();
    });
});

document.getElementById('btnSaveSection')?.addEventListener('click', function () {
    const title   = document.getElementById('sectionTitleInput').value.trim();
    const editId  = document.getElementById('editingSectionId').value;
    if (!title) { alert('Please enter a section title.'); return; }

    const isEdit  = !!editId;
    const url     = isEdit
        ? `<?= url('elms/courses') ?>/${COURSE_ID}/sections/${editId}`
        : `<?= url('elms/courses') ?>/${COURSE_ID}/sections`;

    fetch(url, { method: 'POST', headers: ajaxHeaders, body: JSON.stringify({ title }) })
        .then(r => r.json())
        .then(d => { if (d.status === 'ok') { sectionModal.hide(); location.reload(); } });
});

// Delete section
document.querySelectorAll('.btn-del-section').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm('Delete this section and all its lessons?')) return;
        fetch(`<?= url('elms/courses') ?>/${COURSE_ID}/sections/${this.dataset.id}/delete`, {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': CSRF_TOKEN}
        }).then(r => r.json()).then(d => { if (d.status==='ok') location.reload(); });
    });
});
</script>
