<?php $pageTitle = e($subject['subject_code']).' — '.$subject['subject_name']; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold fs-5"
             style="width:56px;height:56px;background:linear-gradient(135deg,#4f46e5,#7c3aed);letter-spacing:-.5px">
            <?= strtoupper(substr($subject['subject_code'],0,2)) ?>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold"><?= e($subject['subject_name']) ?></h4>
                <?php if($subject['status']==='active'): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                <?php else: ?>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                <?php endif; ?>
                <?php if($subject['is_elective']): ?>
                <span class="badge" style="background:rgba(139,92,246,.1);color:#7c3aed;border:1px solid rgba(139,92,246,.3)"><i class="fas fa-star me-1" style="font-size:.6rem"></i>Elective</span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-3 text-muted small">
                <span><span class="badge bg-secondary me-1"><?= e($subject['subject_code']) ?></span></span>
                <?php if($subject['dept_name']): ?><span><i class="fas fa-sitemap me-1"></i><?= e($subject['dept_name']) ?></span><?php endif; ?>
                <?php if($subject['course_name']): ?><span><i class="fas fa-graduation-cap me-1"></i><?= e($subject['course_name']) ?></span><?php endif; ?>
                <?php if($subject['semester']): ?><span><i class="fas fa-list-ol me-1"></i>Semester <?= $subject['semester'] ?></span><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/subjects') ?>" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <a href="<?= url('academic/subjects/edit/'.$subject['id']) ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        <div class="dropdown">
            <button class="btn btn-light border dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <form method="POST" action="<?= url('academic/subjects/'.$subject['id'].'/duplicate') ?>">
                        <?= csrfField() ?>
                        <button class="dropdown-item"><i class="fas fa-copy me-2 text-secondary"></i>Duplicate</button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?= url('academic/subjects/delete/'.$subject['id']) ?>" onsubmit="return confirm('Delete this subject?')">
                        <?= csrfField() ?>
                        <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT: Details -->
    <div class="col-lg-8">

        <!-- Credit & Hours Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-0 text-center">
                    <?php
                    $kpis = [
                        ['val'=>number_format($subject['credits'],1), 'label'=>'Credits',        'icon'=>'fa-medal',      'color'=>'#4f46e5'],
                        ['val'=>$subject['hours_per_week'].'h',       'label'=>'Hours / Week',   'icon'=>'fa-clock',      'color'=>'#10b981'],
                        ['val'=>$subject['theory_hours'].'h',         'label'=>'Theory Hours',   'icon'=>'fa-chalkboard', 'color'=>'#f59e0b'],
                        ['val'=>$subject['lab_hours'].'h',            'label'=>'Lab Hours',      'icon'=>'fa-flask',      'color'=>'#ef4444'],
                        ['val'=>$subject['tutorial_hours'].'h',       'label'=>'Tutorial Hrs',   'icon'=>'fa-users',      'color'=>'#06b6d4'],
                    ];
                    foreach($kpis as $i=>$k):
                    ?>
                    <div class="col border-end last-no-border">
                        <div class="py-3">
                            <i class="fas <?= $k['icon'] ?> mb-2" style="color:<?= $k['color'] ?>;font-size:1.2rem"></i>
                            <div class="fw-bold fs-5" style="color:<?= $k['color'] ?>"><?= $k['val'] ?></div>
                            <div class="text-muted small"><?= $k['label'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Subject Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom py-3">
                <i class="fas fa-info-circle me-2 text-primary"></i>Subject Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $fields = [
                        ['Subject Type',   ucfirst($subject['subject_type'])],
                        ['Short Name',     $subject['short_name']??'—'],
                        ['Short Label',    $subject['short_label']??'—'],
                        ['Regulation',     $subject['regulation']??'—'],
                        ['Department',     $subject['dept_name']??'—'],
                        ['Course',         $subject['course_name']??'—'],
                        ['Semester',       $subject['semester'] ? 'Semester '.$subject['semester'] : '—'],
                        ['Affects GPA',    ($subject['affects_gpa']??0) ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Yes</span>' : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No</span>'],
                    ];
                    foreach($fields as [$lbl,$val]):
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="text-muted small fw-semibold flex-shrink-0" style="min-width:120px"><?= $lbl ?></div>
                            <div class="small fw-semibold text-dark"><?= $val /* already escaped or is HTML badge */ ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if($subject['description']): ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Description</div>
                        <div class="small text-dark"><?= e($subject['description']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($subject['syllabus_url']): ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Syllabus</div>
                        <a href="<?= e($subject['syllabus_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Open Syllabus
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Curriculum & Governance -->
        <?php
        $hasGov = ($subject['delivery_mode']??'') || ($subject['priority_level']??'') || ($subject['curriculum_stream']??'')
                || ($subject['architecture']??'') || ($subject['governing_body']??'') || ($subject['review_authority']??'')
                || ($subject['grading_scale']??'') || ($subject['external_exam_code']??'')
                || ($subject['valid_from']??'') || ($subject['valid_until']??'')
                || ($subject['is_sub_module']??0) || ($subject['attach_syllabus']??0) || ($subject['track_sessions']??0)
                || ($subject['local_language']??0) || ($subject['secondary_language']??0);
        if($hasGov):
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom py-3">
                <i class="fas fa-sitemap me-2 text-info"></i>Curriculum &amp; Governance
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $govFields = array_filter([
                        ['Delivery Mode',     $subject['delivery_mode']??''],
                        ['Priority Level',    $subject['priority_level']??''],
                        ['Curriculum Stream', $subject['curriculum_stream']??''],
                        ['Architecture',      $subject['architecture']??''],
                        ['Governing Body',    $subject['governing_body']??''],
                        ['Review Authority',  $subject['review_authority']??''],
                        ['Grading Scale',     $subject['grading_scale']??''],
                        ['Ext. Exam Code',    $subject['external_exam_code']??''],
                        ['Valid From',        $subject['valid_from'] ? date('d M Y', strtotime($subject['valid_from'])) : ''],
                        ['Valid Until',       $subject['valid_until'] ? date('d M Y', strtotime($subject['valid_until'])) : ''],
                    ], fn($r) => $r[1] !== '');
                    foreach($govFields as [$lbl,$val]):
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="text-muted small fw-semibold flex-shrink-0" style="min-width:130px"><?= $lbl ?></div>
                            <div class="small fw-semibold text-dark"><?= e($val) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php
                    $flags = array_filter([
                        'Sub-module'        => $subject['is_sub_module']??0,
                        'Attach Syllabus'   => $subject['attach_syllabus']??0,
                        'Track Sessions'    => $subject['track_sessions']??0,
                        'Local Language'    => $subject['local_language']??0,
                        'Secondary Language'=> $subject['secondary_language']??0,
                    ]);
                    if($flags):
                    ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-2">Feature Flags</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($flags as $flag=>$on): if($on): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fas fa-check-circle me-1"></i><?= $flag ?>
                            </span>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Faculty Allocated -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between pt-4 pb-2">
                <span class="fw-semibold"><i class="fas fa-chalkboard-teacher me-2 text-info"></i>Faculty Allocated</span>
                <a href="<?= url('academic/faculty-allocation/create') ?>" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-plus me-1"></i>Assign Faculty
                </a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($facultyList)): ?>
                <div class="text-center py-4 text-muted small"><i class="fas fa-user-slash fa-2x d-block mb-2 opacity-25"></i>No faculty assigned yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">Faculty</th><th>Batch</th><th>Section</th><th class="text-center">Type</th><th class="text-center">Hrs/Wk</th></tr></thead>
                        <tbody>
                        <?php foreach($facultyList as $f): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold small"><?= e($f['faculty_name']) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= e($f['faculty_email']) ?></div>
                            </td>
                            <td class="small"><?= $f['program_name'] ? e($f['program_name']).' ('.e($f['batch_term']).')' : '—' ?></td>
                            <td class="small"><?= $f['section_name'] ? e($f['section_name']) : '—' ?></td>
                            <td class="text-center">
                                <?php $tc=['theory'=>'primary','lab'=>'warning','both'=>'info'][$f['allocation_type']]??'secondary'; ?>
                                <span class="badge bg-<?= $tc ?>-subtle text-<?= $tc ?> border border-<?= $tc ?>-subtle"><?= ucfirst($f['allocation_type']) ?></span>
                            </td>
                            <td class="text-center fw-bold small"><?= $f['hours_per_week'] ?>h</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- LMS Materials -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between pt-4 pb-2">
                <span class="fw-semibold"><i class="fas fa-folder-open me-2 text-success"></i>Recent LMS Materials</span>
                <a href="<?= url('academic/lms?subject_id='.$subject['id']) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-external-link-alt me-1"></i>View All
                </a>
            </div>
            <div class="card-body">
                <?php if(empty($materials)): ?>
                <div class="text-center py-3 text-muted small"><i class="fas fa-file fa-2x d-block mb-2 opacity-25"></i>No materials uploaded yet.</div>
                <?php else: ?>
                <div class="row g-2">
                    <?php
                    $typeIcon = ['notes'=>'fa-file-alt','video'=>'fa-video','assignment'=>'fa-tasks','quiz'=>'fa-question','announcement'=>'fa-bullhorn','reference'=>'fa-link','lab_manual'=>'fa-flask','other'=>'fa-file'];
                    $typeClr  = ['notes'=>'primary','video'=>'danger','assignment'=>'warning','quiz'=>'success','announcement'=>'info','reference'=>'secondary','lab_manual'=>'dark','other'=>'secondary'];
                    foreach($materials as $m):
                        $icon = $typeIcon[$m['material_type']]??'fa-file';
                        $clr  = $typeClr[$m['material_type']]??'secondary';
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 p-2 border rounded-2">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:rgba(79,70,229,.08)">
                                <i class="fas <?= $icon ?> text-<?= $clr ?>" style="font-size:.85rem"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-semibold small text-truncate"><?= e($m['title']) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= ucfirst(str_replace('_',' ',$m['material_type'])) ?> · <?= e($m['faculty_name']) ?></div>
                            </div>
                            <?php if($m['file_path']): ?>
                            <a href="<?= url('academic/lms/'.$m['id'].'/download') ?>" class="btn btn-sm btn-light ms-auto flex-shrink-0"><i class="fas fa-download"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: Sidebar -->
    <div class="col-lg-4">

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold pt-4 pb-2"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</div>
            <div class="list-group list-group-flush">
                <a href="<?= url('academic/subjects/edit/'.$subject['id']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="fas fa-edit text-primary" style="width:18px"></i><span>Edit Subject</span><i class="fas fa-chevron-right ms-auto text-muted small"></i>
                </a>
                <a href="<?= url('academic/lms/create') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="fas fa-upload text-success" style="width:18px"></i><span>Upload Material</span><i class="fas fa-chevron-right ms-auto text-muted small"></i>
                </a>
                <a href="<?= url('academic/faculty-allocation/create') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="fas fa-user-plus text-info" style="width:18px"></i><span>Assign Faculty</span><i class="fas fa-chevron-right ms-auto text-muted small"></i>
                </a>
                <a href="<?= url('academic/assessments/create') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="fas fa-clipboard-list text-warning" style="width:18px"></i><span>Create Assessment</span><i class="fas fa-chevron-right ms-auto text-muted small"></i>
                </a>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold pt-4 pb-2"><i class="fas fa-calendar-check me-2 text-primary"></i>Attendance Summary</div>
            <div class="card-body">
                <?php
                $sessions = (int)($attSummary['sessions']??0);
                $present  = (int)($attSummary['total_present']??0);
                $absent   = (int)($attSummary['total_absent']??0);
                $total    = $present + $absent;
                $pct      = $total > 0 ? round($present/$total*100) : 0;
                ?>
                <?php if($sessions===0): ?>
                <div class="text-center text-muted small py-2"><i class="fas fa-calendar fa-2x d-block mb-2 opacity-25"></i>No sessions recorded yet.</div>
                <?php else: ?>
                <div class="text-center mb-3">
                    <div class="fs-1 fw-bold text-primary"><?= $pct ?>%</div>
                    <div class="text-muted small">Overall Attendance</div>
                </div>
                <div class="progress mb-3" style="height:8px">
                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="fw-bold text-dark"><?= $sessions ?></div>
                        <div class="text-muted" style="font-size:.7rem">Sessions</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success"><?= number_format($present) ?></div>
                        <div class="text-muted" style="font-size:.7rem">Present</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-danger"><?= number_format($absent) ?></div>
                        <div class="text-muted" style="font-size:.7rem">Absent</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Assessments -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between pt-4 pb-2">
                <span class="fw-semibold"><i class="fas fa-clipboard-list me-2 text-warning"></i>Assessments</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><?= count($assessments) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if(empty($assessments)): ?>
                <div class="text-center py-4 text-muted small"><i class="fas fa-clipboard fa-2x d-block mb-2 opacity-25"></i>No assessments created.</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach(array_slice($assessments,0,6) as $a): ?>
                    <div class="list-group-item border-0 px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small fw-semibold"><?= e($a['assessment_name']) ?></div>
                                <div class="text-muted" style="font-size:.7rem">
                                    <?= ucfirst($a['assessment_type']) ?> · Max: <?= $a['max_marks'] ?> · Pass: <?= $a['passing_marks'] ?>
                                </div>
                            </div>
                            <?php $ac=['active'=>'success','completed'=>'info','archived'=>'secondary'][$a['status']]??'secondary'; ?>
                            <span class="badge bg-<?= $ac ?>-subtle text-<?= $ac ?> border border-<?= $ac ?>-subtle"><?= ucfirst($a['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Meta -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted mb-1">Created by</div>
                <div class="small fw-semibold mb-2"><?= e(trim(($subject['creator_first']??'').' '.($subject['creator_last']??'')) ?: 'System') ?></div>
                <div class="small text-muted mb-1">Created at</div>
                <div class="small fw-semibold mb-2"><?= date('d M Y, h:i A', strtotime($subject['created_at'])) ?></div>
                <div class="small text-muted mb-1">Last updated</div>
                <div class="small fw-semibold"><?= date('d M Y, h:i A', strtotime($subject['updated_at'])) ?></div>
            </div>
        </div>

    </div>
</div>
