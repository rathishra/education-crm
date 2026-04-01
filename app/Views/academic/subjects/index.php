<?php $pageTitle = 'Subject Management'; ?>

<!-- ================================================================
     HEADER
     ================================================================ -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Subject Management</h4>
        <p class="text-muted small mb-0">Manage curriculum subjects across semesters, departments and courses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/subjects/export') ?>" class="btn btn-light border">
            <i class="fas fa-file-csv me-1 text-success"></i>Export CSV
        </a>
        <a href="<?= url('academic/subjects/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Subject
        </a>
    </div>
</div>

<!-- ================================================================
     STAT CARDS
     ================================================================ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #4f46e5!important;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">Total</div>
                        <div class="fs-2 fw-bold text-dark"><?= number_format($stats['total']??0) ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(79,70,229,.1)">
                        <i class="fas fa-book text-primary"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted"><?= $stats['active']??0 ?> active · <?= $stats['inactive']??0 ?> inactive</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #10b981!important;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">Theory</div>
                        <div class="fs-2 fw-bold text-dark"><?= $stats['theory']??0 ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(16,185,129,.1)">
                        <i class="fas fa-chalkboard text-success"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted"><?= $stats['tutorial']??0 ?> tutorial · <?= $stats['project']??0 ?> project</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b!important;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">Lab</div>
                        <div class="fs-2 fw-bold text-dark"><?= $stats['lab']??0 ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(245,158,11,.1)">
                        <i class="fas fa-flask text-warning"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">Practical &amp; lab sessions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #8b5cf6!important;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">Elective</div>
                        <div class="fs-2 fw-bold text-dark"><?= $stats['elective']??0 ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(139,92,246,.1)">
                        <i class="fas fa-star" style="color:#8b5cf6"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">Optional / open electives</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #06b6d4!important;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">Total Credits</div>
                        <div class="fs-2 fw-bold text-dark"><?= $stats['total_credits']??0 ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(6,182,212,.1)">
                        <i class="fas fa-medal" style="color:#06b6d4"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">Avg <?= $stats['avg_credits']??0 ?> per subject</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <!-- Semester breakdown mini-chart -->
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f43f5e!important;">
            <div class="card-body py-3 px-3">
                <div class="text-muted small fw-semibold text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.05em">Sem Breakdown</div>
                <?php foreach(array_slice($semBreakdown,0,4) as $sb): ?>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted">Sem <?= $sb['semester'] ?></span>
                    <div class="d-flex align-items-center gap-1">
                        <div style="width:50px;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                            <div style="width:<?= min(100, ($sb['cnt']/max(1,$stats['total']))*100*3) ?>%;height:100%;background:#f43f5e;border-radius:3px"></div>
                        </div>
                        <span class="small fw-semibold"><?= $sb['cnt'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(count($semBreakdown) > 4): ?><div class="small text-muted">+<?= count($semBreakdown)-4 ?> more</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     FILTER BAR
     ================================================================ -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="&#128269; Code / Name…" value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach(['theory'=>'Theory','lab'=>'Lab','tutorial'=>'Tutorial','project'=>'Project','elective'=>'Elective'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $type===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select name="semester" class="form-select form-select-sm">
                    <option value="">All Sem</option>
                    <?php for($i=1;$i<=10;$i++): ?>
                    <option value="<?= $i ?>" <?= $sem==$i?'selected':'' ?>>Sem <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="dept_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    <?php foreach($departments as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $deptId==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['code']) ?> — <?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Status</option>
                    <option value="active"   <?= $status==='active'  ?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1 align-items-center">
                <button class="btn btn-sm btn-primary px-3"><i class="fas fa-search"></i></button>
                <a href="<?= url('academic/subjects') ?>" class="btn btn-sm btn-light border" title="Reset">
                    <i class="fas fa-redo"></i>
                </a>
                <!-- Group toggle -->
                <div class="ms-auto">
                    <a href="<?= url('academic/subjects') ?>?<?= http_build_query(array_merge($_GET,['group_by'=>$groupBy==='semester'?'':'semester'])) ?>"
                       class="btn btn-sm <?= $groupBy==='semester' ? 'btn-primary' : 'btn-light border' ?>" title="Group by Semester">
                        <i class="fas fa-layer-group"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================
     BULK ACTION BAR (visible when rows selected)
     ================================================================ -->
<div id="bulkBar" class="alert alert-primary d-none mb-3 py-2 px-3 d-flex align-items-center gap-3 shadow-sm">
    <span class="fw-semibold"><span id="bulkCount">0</span> selected</span>
    <div class="ms-auto d-flex gap-2">
        <form method="POST" action="<?= url('academic/subjects/bulk') ?>" id="bulkForm">
            <?= csrfField() ?>
            <div id="bulkIdsContainer"></div>
            <input type="hidden" name="bulk_action" id="bulkActionInput">
            <button type="button" class="btn btn-sm btn-success" onclick="submitBulk('activate')"><i class="fas fa-check me-1"></i>Activate</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="submitBulk('deactivate')"><i class="fas fa-ban me-1"></i>Deactivate</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="if(confirm('Delete selected?')) submitBulk('delete')"><i class="fas fa-trash me-1"></i>Delete</button>
        </form>
        <button class="btn btn-sm btn-light border" onclick="clearSelection()"><i class="fas fa-times me-1"></i>Clear</button>
    </div>
</div>

<!-- ================================================================
     TABLE / GROUPED VIEW
     ================================================================ -->
<?php if ($groupBy === 'semester' && !empty($grouped)): ?>

    <?php foreach ($grouped as $semLabel => $semSubjects): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary"><?= e($semLabel) ?></span>
                <span class="small text-muted"><?= count($semSubjects) ?> subjects</span>
                <?php $semCredits = array_sum(array_column($semSubjects,'credits')); ?>
                <span class="small text-muted">· <?= number_format($semCredits,1) ?> credits total</span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php include __DIR__ . '/_subject_table_rows.php'; $subjectsForTable = $semSubjects; include __DIR__ . '/_table_partial.php'; ?>
            <table class="table table-hover align-middle mb-0">
                <?= renderSubjectTableHead() ?>
                <tbody>
                    <?= renderSubjectRows($semSubjects) ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-4">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" id="selectAll" class="form-check-input" title="Select all">
            <span class="small text-muted ms-1"><?= count($subjects) ?> subjects</span>
        </div>
        <small class="text-muted">Sorted by semester → code</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="subjectsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" width="36"><input type="checkbox" id="selectAllHead" class="form-check-input"></th>
                        <th>Code</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th class="text-center">Sem</th>
                        <th class="text-center">Credits</th>
                        <th class="text-center">Hours</th>
                        <th>Department</th>
                        <th class="text-center">Links</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($subjects)): ?>
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-book fa-3x d-block mb-3 opacity-25"></i>
                            <div class="fw-semibold mb-1">No subjects found</div>
                            <div class="small mb-3">Try adjusting filters or add your first subject.</div>
                            <a href="<?= url('academic/subjects/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Subject</a>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($subjects as $s):
                    $typeClr = ['theory'=>'primary','lab'=>'warning','tutorial'=>'info','project'=>'success','elective'=>'secondary'][$s['subject_type']]??'dark';
                ?>
                <tr class="subject-row" data-id="<?= $s['id'] ?>">
                    <td class="ps-4"><input type="checkbox" class="form-check-input row-check" value="<?= $s['id'] ?>"></td>
                    <td>
                        <a href="<?= url('academic/subjects/'.$s['id']) ?>" class="fw-bold text-dark text-decoration-none">
                            <span class="badge bg-dark"><?= e($s['subject_code']) ?></span>
                        </a>
                        <?php if($s['regulation']): ?><div class="text-muted" style="font-size:.7rem">R: <?= e($s['regulation']) ?></div><?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('academic/subjects/'.$s['id']) ?>" class="text-decoration-none">
                            <div class="fw-semibold text-dark"><?= e($s['subject_name']) ?></div>
                            <?php if($s['short_name']): ?><div class="text-muted small"><?= e($s['short_name']) ?></div><?php endif; ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-<?= $typeClr ?>-subtle text-<?= $typeClr ?> border border-<?= $typeClr ?>-subtle">
                            <?= ucfirst($s['subject_type']) ?>
                        </span>
                        <?php if($s['is_elective']): ?>
                        <span class="badge bg-purple-subtle text-purple border" style="background:rgba(139,92,246,.1);color:#7c3aed;border-color:rgba(139,92,246,.3)!important">
                            <i class="fas fa-star" style="font-size:.6rem"></i> Elective
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if($s['semester']): ?>
                        <span class="badge bg-light text-dark border fw-semibold">S<?= $s['semester'] ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold text-primary fs-6"><?= number_format($s['credits'],1) ?></span>
                        <div class="text-muted" style="font-size:.7rem">credits</div>
                    </td>
                    <td class="text-center">
                        <div class="fw-semibold"><?= $s['hours_per_week'] ?>h</div>
                        <div style="font-size:.65rem;color:#94a3b8">
                            <?php $parts=[];
                            if($s['theory_hours'])  $parts[]=$s['theory_hours'].'T';
                            if($s['lab_hours'])      $parts[]=$s['lab_hours'].'L';
                            if($s['tutorial_hours']) $parts[]=$s['tutorial_hours'].'Tu';
                            echo implode('+',$parts);
                            ?>
                        </div>
                    </td>
                    <td>
                        <div class="small text-dark"><?= e($s['dept_name'] ?? '—') ?></div>
                        <?php if($s['course_name']): ?><div class="text-muted" style="font-size:.7rem"><?= e($s['course_code']) ?></div><?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <?php if($s['faculty_count']>0): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle" title="Faculty assigned">
                                <i class="fas fa-chalkboard-teacher"></i> <?= $s['faculty_count'] ?>
                            </span>
                            <?php endif; ?>
                            <?php if($s['material_count']>0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="LMS materials">
                                <i class="fas fa-file-alt"></i> <?= $s['material_count'] ?>
                            </span>
                            <?php endif; ?>
                            <?php if($s['assessment_count']>0): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="Assessments">
                                <i class="fas fa-clipboard-list"></i> <?= $s['assessment_count'] ?>
                            </span>
                            <?php endif; ?>
                            <?php if(!$s['faculty_count'] && !$s['material_count'] && !$s['assessment_count']): ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm p-0 border-0 toggle-status"
                                data-id="<?= $s['id'] ?>"
                                title="Click to toggle status">
                            <span class="badge bg-<?= $s['status']==='active'?'success':'danger' ?>-subtle text-<?= $s['status']==='active'?'success':'danger' ?> border border-<?= $s['status']==='active'?'success':'danger' ?>-subtle status-badge">
                                <i class="fas fa-circle me-1" style="font-size:.45rem;vertical-align:middle"></i><?= ucfirst($s['status']) ?>
                            </span>
                        </button>
                        <!-- hidden CSRF form for toggle -->
                        <form class="toggle-form d-none" method="POST" action="<?= url('academic/subjects/'.$s['id'].'/toggle-status') ?>">
                            <?= csrfField() ?>
                        </form>
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="<?= url('academic/subjects/'.$s['id']) ?>"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                <li><a class="dropdown-item" href="<?= url('academic/subjects/edit/'.$s['id']) ?>"><i class="fas fa-edit me-2 text-info"></i>Edit</a></li>
                                <li>
                                    <form method="POST" action="<?= url('academic/subjects/'.$s['id'].'/duplicate') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <button class="dropdown-item"><i class="fas fa-copy me-2 text-secondary"></i>Duplicate</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?= url('academic/subjects/delete/'.$s['id']) ?>" onsubmit="return confirm('Delete this subject?')">
                                        <?= csrfField() ?>
                                        <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
(function(){
    // ── Select all checkboxes
    const selectAll     = document.getElementById('selectAll');
    const selectAllHead = document.getElementById('selectAllHead');
    const bulkBar       = document.getElementById('bulkBar');
    const bulkCount     = document.getElementById('bulkCount');
    const bulkIdsContainer = document.getElementById('bulkIdsContainer');

    function getChecked() {
        return Array.from(document.querySelectorAll('.row-check:checked')).map(c=>c.value);
    }
    function updateBulkBar() {
        const ids = getChecked();
        if (ids.length > 0) {
            bulkBar.classList.remove('d-none');
            bulkCount.textContent = ids.length;
        } else {
            bulkBar.classList.add('d-none');
        }
    }
    function clearSelection() {
        document.querySelectorAll('.row-check').forEach(c=>c.checked=false);
        if(selectAll) selectAll.checked=false;
        if(selectAllHead) selectAllHead.checked=false;
        updateBulkBar();
    }
    window.clearSelection = clearSelection;

    [selectAll, selectAllHead].forEach(el => {
        if(!el) return;
        el.addEventListener('change', function(){
            document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked);
            updateBulkBar();
        });
    });
    document.querySelectorAll('.row-check').forEach(c=>c.addEventListener('change', updateBulkBar));

    window.submitBulk = function(action) {
        const ids = getChecked();
        if (!ids.length) return;
        document.getElementById('bulkActionInput').value = action;
        bulkIdsContainer.innerHTML = ids.map(id=>`<input type="hidden" name="ids[]" value="${id}">`).join('');
        document.getElementById('bulkForm').submit();
    };

    // ── Status toggle (AJAX)
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function(){
            const id   = this.dataset.id;
            const form = this.closest('tr').querySelector('.toggle-form');
            const csrf = form.querySelector('input[name=_token]')?.value || form.querySelector('input[name="csrf_token"]')?.value || '';
            const badge = this.querySelector('.status-badge');

            fetch(form.action, {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({_token: csrf, csrf_token: csrf})
            })
            .then(r=>r.json())
            .then(data=>{
                const isActive = data.status === 'active';
                badge.className = `badge bg-${isActive?'success':'danger'}-subtle text-${isActive?'success':'danger'} border border-${isActive?'success':'danger'}-subtle status-badge`;
                badge.innerHTML = `<i class="fas fa-circle me-1" style="font-size:.45rem;vertical-align:middle"></i>${data.status.charAt(0).toUpperCase()+data.status.slice(1)}`;
                // Show toast
                if(window.toastr) toastr.success(data.message);
            })
            .catch(()=>{ if(window.toastr) toastr.error('Toggle failed. Please refresh.'); });
        });
    });

    // ── DataTable (optional)
    if (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#subjectsTable').DataTable({
            paging: true, pageLength: 25, ordering: true, searching: false,
            columnDefs: [{orderable:false, targets:[0,8,9,10]}],
            language: {paginate:{previous:'‹',next:'›'}}
        });
    }
})();
</script>
