<?php $pageTitle = 'LMS — Study Materials'; ?>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <?php
    $typeInfo = [
        'notes'       => ['Notes',        'fa-file-alt',      'primary'],
        'video'       => ['Videos',       'fa-video',         'danger'],
        'assignment'  => ['Assignments',  'fa-tasks',         'warning'],
        'quiz'        => ['Quiz',         'fa-question-circle','success'],
        'announcement'=> ['Announcements','fa-bullhorn',      'info'],
        'reference'   => ['References',  'fa-link',          'secondary'],
        'lab_manual'  => ['Lab Manuals', 'fa-flask',         'dark'],
    ];
    foreach($typeInfo as $key=>[$label,$icon,$color]): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-<?= $color ?>"><?= $stats[$key]??0 ?></div>
            <div class="text-muted small"><i class="fas <?= $icon ?> me-1"></i><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters + Add -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="subject_id" class="form-select form-select-sm select2">
                    <option value="">All Subjects</option>
                    <?php foreach($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $subjectId==$s['id']?'selected':'' ?>><?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="batch_id" class="form-select form-select-sm select2">
                    <option value="">All Batches</option>
                    <?php foreach($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchId==$b['id']?'selected':'' ?>><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach(array_keys($typeInfo) as $t): ?>
                    <option value="<?= $t ?>" <?= $type===$t?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-sm btn-primary px-3"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('academic/lms') ?>" class="btn btn-sm btn-light">Reset</a>
                <a href="<?= url('academic/lms/create') ?>" class="btn btn-sm btn-success ms-auto"><i class="fas fa-upload me-1"></i>Upload Material</a>
            </div>
        </form>
    </div>
</div>

<!-- Materials Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Faculty</th>
                        <th>Published</th>
                        <th class="text-center">Downloads</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($materials)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-2x d-block mb-2 opacity-25"></i>No materials uploaded yet.
                    </td></tr>
                    <?php else: foreach($materials as $m):
                        [$ml,$mi,$mc] = $typeInfo[$m['material_type']] ?? ['Other','fa-file','secondary'];
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?= e($m['title']) ?></div>
                            <?php if($m['description']): ?><div class="text-muted small"><?= e(substr($m['description'],0,60)) ?>…</div><?php endif; ?>
                            <?php if($m['due_date']): ?><div class="small text-danger"><i class="fas fa-clock me-1"></i>Due: <?= date('d M Y',strtotime($m['due_date'])) ?></div><?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= e($m['subject_code']) ?></span><br><small><?= e($m['subject_name']) ?></small></td>
                        <td><span class="badge bg-<?= $mc ?>-subtle text-<?= $mc ?> border border-<?= $mc ?>-subtle"><i class="fas <?= $mi ?> me-1"></i><?= $ml ?></span></td>
                        <td class="small"><?= e($m['faculty_name']) ?></td>
                        <td class="small text-muted"><?= $m['publish_date'] ? date('d M Y',strtotime($m['publish_date'])) : '—' ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= number_format($m['download_count']) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $m['is_published']?'success':'secondary' ?>-subtle text-<?= $m['is_published']?'success':'secondary' ?> border border-<?= $m['is_published']?'success':'secondary' ?>-subtle">
                                <?= $m['is_published'] ? 'Published' : 'Draft' ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <?php if($m['file_path']): ?>
                            <a href="<?= url('academic/lms/'.$m['id'].'/download') ?>" class="btn btn-sm btn-light text-success" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php elseif($m['video_link']): ?>
                            <a href="<?= e($m['video_link']) ?>" target="_blank" class="btn btn-sm btn-light text-danger" title="Watch">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <?php elseif($m['external_link']): ?>
                            <a href="<?= e($m['external_link']) ?>" target="_blank" class="btn btn-sm btn-light text-primary" title="Open Link">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('academic/lms/'.$m['id'].'/delete') ?>" class="d-inline"
                                  onsubmit="return confirm('Delete this material?')">
                                <?= csrfField() ?>
                                <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
});
</script>
