<?php $pageTitle = 'Faculty Allocation'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Faculty / Subject Allocation</h4>
        <p class="text-muted small mb-0">Assign faculty members to subjects, batches and sections.</p>
    </div>
    <a href="<?= url('academic/faculty-allocation/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Allocate Faculty</a>
</div>

<!-- Workload Summary -->
<?php if(!empty($workload)): ?>
<div class="row g-3 mb-4">
    <?php foreach(array_slice($workload,0,4) as $w): ?>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                    <i class="fas fa-chalkboard-teacher text-primary"></i>
                </div>
                <div>
                    <div class="fw-semibold"><?= e($w['faculty_name']) ?></div>
                    <div class="small text-muted"><?= $w['subject_count'] ?> subjects · <?= $w['total_hours'] ?>h/wk</div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <select name="batch_id" class="form-select form-select-sm select2">
                    <option value="">All Batches</option>
                    <?php foreach($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchId==$b['id']?'selected':'' ?>><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="subject_id" class="form-select form-select-sm select2">
                    <option value="">All Subjects</option>
                    <?php foreach($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $subjectId==$s['id']?'selected':'' ?>><?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-sm btn-primary px-3"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('academic/faculty-allocation') ?>" class="btn btn-sm btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Faculty</th>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Section</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Hrs/Wk</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($allocations)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-chalkboard-teacher fa-2x d-block mb-2 opacity-25"></i>No allocations found.</td></tr>
                    <?php else: foreach($allocations as $a): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?= e($a['faculty_name']) ?></div>
                            <div class="text-muted small"><?= e($a['faculty_email']) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary me-1"><?= e($a['subject_code']) ?></span>
                            <?= e($a['subject_name']) ?>
                            <div class="text-muted small"><?= number_format($a['credits'],1) ?> credits</div>
                        </td>
                        <td><?= $a['program_name'] ? e($a['program_name']).' ('.e($a['batch_term']).')' : '—' ?></td>
                        <td><?= $a['section_name'] ? e($a['section_name']) : '—' ?></td>
                        <td class="text-center">
                            <?php $tc = ['theory'=>'primary','lab'=>'warning','both'=>'info'][$a['allocation_type']]??'secondary'; ?>
                            <span class="badge bg-<?= $tc ?>-subtle text-<?= $tc ?> border border-<?= $tc ?>-subtle"><?= ucfirst($a['allocation_type']) ?></span>
                            <?php if($a['lab_batch_number']): ?><div class="text-muted small">Lab Batch <?= $a['lab_batch_number'] ?></div><?php endif; ?>
                        </td>
                        <td class="text-center fw-bold"><?= $a['hours_per_week'] ?>h</td>
                        <td class="text-end pe-4">
                            <form method="POST" action="<?= url('academic/faculty-allocation/'.$a['id'].'/delete') ?>" class="d-inline"
                                  onsubmit="return confirm('Remove this allocation?')">
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
