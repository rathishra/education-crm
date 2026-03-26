<?php $pageTitle = 'Student - ' . e($student['student_id_number']); ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-user-circle me-2"></i><?= e($student['first_name'].' '.($student['last_name']??'')) ?></h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li><li class="breadcrumb-item active"><?= e($student['student_id_number']) ?></li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('students.edit')): ?><a href="<?= url('students/'.$student['id'].'/edit') ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a><?php endif; ?>
        <a href="<?= url('students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <!-- Profile -->
    <div class="col-lg-4">
        <div class="card mb-3 text-center">
            <div class="card-body py-4">
                <div class="avatar-circle mx-auto mb-3" style="width:80px;height:80px;background:var(--bs-primary);border-radius:50%;display:flex;align-items:center;justify-content:center">
                    <span class="fs-1 text-white fw-bold"><?= strtoupper(substr($student['first_name'],0,1)) ?></span>
                </div>
                <h5 class="mb-1"><?= e($student['first_name'].' '.($student['last_name']??'')) ?></h5>
                <code class="text-muted"><?= e($student['student_id_number']) ?></code>
                <div class="mt-2">
                    <?php $sc=['active'=>'success','inactive'=>'secondary','dropped'=>'danger','passed_out'=>'info','suspended'=>'warning']; ?>
                    <span class="badge bg-<?= $sc[$student['status']]??'secondary' ?> fs-6"><?= ucfirst(str_replace('_',' ',$student['status'])) ?></span>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"><small class="text-muted d-block">Phone</small><?= e($student['phone']) ?></div>
                <div class="mb-2"><small class="text-muted d-block">Email</small><?= e($student['email']??'-') ?></div>
                <div class="mb-2"><small class="text-muted d-block">Course</small><?= e($student['course_name']??'-') ?></div>
                <div class="mb-2"><small class="text-muted d-block">Batch</small><?= e($student['batch_name']??'-') ?></div>
                <div class="mb-2"><small class="text-muted d-block">Department</small><?= e($student['department_name']??'-') ?></div>
                <div><small class="text-muted d-block">Admission Date</small><?= $student['admission_date'] ? formatDate($student['admission_date']) : '-' ?></div>
            </div>
        </div>
        <!-- Fee Summary -->
        <?php 
           $feeSql = "SELECT SUM(net_amount) as total_net, SUM(paid_amount) as total_paid FROM student_fees WHERE student_id = ? AND status != 'waived'";
           db()->query($feeSql, [$student['id']]);
           $fs = db()->fetch();
           $totalNet = $fs['total_net'] ?? 0;
           $totalPaid = $fs['total_paid'] ?? 0;
           $totalBalance = $totalNet - $totalPaid;
        ?>
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span><i class="fas fa-rupee-sign me-2"></i>Fee Summary</span>
                <a href="<?= url('payments/collect/'.$student['id']) ?>" class="btn btn-xs btn-primary">Pay Now</a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Net Payable</span><span class="fw-semibold"><?= formatCurrency($totalNet) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Paid</span><span class="text-success fw-semibold"><?= formatCurrency($totalPaid) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Balance</span><span class="text-danger fw-semibold"><?= formatCurrency($totalBalance) ?></span></div>
            </div>
        </div>
    </div>

    <!-- Tabbed Details -->
    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-3" id="studentTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button" role="tab"><i class="fas fa-user mb-1 d-block"></i>Profile</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-academic" type="button" role="tab"><i class="fas fa-graduation-cap mb-1 d-block"></i>Academic</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parents" type="button" role="tab"><i class="fas fa-users mb-1 d-block"></i>Parents</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab"><i class="fas fa-paperclip mb-1 d-block"></i>Documents</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fees" type="button" role="tab"><i class="fas fa-rupee-sign mb-1 d-block"></i>Fees</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab"><i class="fas fa-calendar-check mb-1 d-block"></i>Attendance</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exams" type="button" role="tab"><i class="fas fa-file-signature mb-1 d-block"></i>Exam</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timeline" type="button" role="tab"><i class="fas fa-history mb-1 d-block"></i>Timeline</button></li>
        </ul>

        <div class="tab-content border-top-0" id="studentTabsContent">
            
            <!-- Profile Tab -->
            <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-3"><i class="fas fa-id-card me-2"></i>Personal Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4"><small class="text-muted d-block">Date of Birth</small><span class="fw-semibold"><?= e($student['date_of_birth'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Gender</small><span class="fw-semibold"><?= ucfirst($student['gender'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Blood Group</small><span class="fw-semibold"><?= e($student['blood_group'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Religion</small><span class="fw-semibold"><?= e($student['religion'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Category</small><span class="fw-semibold"><?= strtoupper(e($student['category'] ?? '-')) ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Nationality</small><span class="fw-semibold"><?= e($student['nationality'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Aadhaar / ID</small><span class="fw-semibold"><?= e($student['aadhar_number'] ?? '-') ?></span></div>
                        </div>
                        <hr>
                        <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                        <div class="row g-3">
                            <div class="col-12"><small class="text-muted d-block">Current Address</small><?= e($student['address_line1'] ?? '') ?> <?= e($student['address_line2'] ?? '') ?><br><?= e($student['city'] ?? '') ?>, <?= e($student['state'] ?? '') ?> - <?= e($student['pincode'] ?? '') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Tab -->
            <div class="tab-pane fade" id="tab-academic" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Previous Academic History</h6>
                        <div class="row g-3">
                            <div class="col-md-4"><small class="text-muted d-block">Last Qualification</small><span class="fw-semibold"><?= e($student['previous_qualification'] ?? '-') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">School/College</small><span class="fw-semibold"><?= e($student['previous_institution'] ?? '-') ?></span></div>
                            <div class="col-md-2"><small class="text-muted d-block">Percentage</small><span class="badge bg-info"><?= $student['previous_percentage'] ? e($student['previous_percentage']).'%' : '-' ?></span></div>
                            <div class="col-md-2"><small class="text-muted d-block">Passing Year</small><span class="fw-semibold"><?= e($student['previous_year_of_passing'] ?? '-') ?></span></div>
                        </div>
                        <hr>
                        <h6 class="text-primary mb-3"><i class="fas fa-university me-2"></i>Current Admission</h6>
                        <div class="row g-3">
                            <div class="col-md-4"><small class="text-muted d-block">Admission Type</small><span class="fw-semibold"><?= ucfirst($student['admission_type'] ?? 'Regular') ?></span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Student Type</small><span class="fw-semibold">Day Scholar</span></div>
                            <div class="col-md-4"><small class="text-muted d-block">Current Semester</small><span class="fw-semibold"><?= e($student['current_semester'] ?? '1') ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parents Tab -->
            <div class="tab-pane fade" id="tab-parents" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-user-tie me-2"></i>Father's Details</h6>
                                <div class="mb-2"><small class="text-muted d-block">Name</small><span class="fw-semibold"><?= e($student['father_name'] ?? '-') ?></span></div>
                                <div class="mb-2"><small class="text-muted d-block">Mobile</small><span><?= e($student['father_phone'] ?? '-') ?></span></div>
                                <div class="mb-2"><small class="text-muted d-block">Email</small><span><?= e($student['father_email'] ?? '-') ?></span></div>
                                <div class="mb-2"><small class="text-muted d-block">Occupation</small><span><?= e($student['father_occupation'] ?? '-') ?></span></div>
                            </div>
                            <div class="col-md-6 border-start">
                                <h6 class="text-primary mb-3"><i class="fas fa-female me-2"></i>Mother's Details</h6>
                                <div class="mb-2"><small class="text-muted d-block">Name</small><span class="fw-semibold"><?= e($student['mother_name'] ?? '-') ?></span></div>
                                <div class="mb-2"><small class="text-muted d-block">Mobile</small><span><?= e($student['mother_phone'] ?? '-') ?></span></div>
                                <div class="mb-2"><small class="text-muted d-block">Occupation</small><span><?= e($student['mother_occupation'] ?? '-') ?></span></div>
                            </div>
                            <div class="col-12 border-top pt-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-user-shield me-2"></i>Guardian Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-3"><small class="text-muted d-block">Relation</small><span class="fw-semibold"><?= e($student['guardian_relation'] ?? 'Other') ?></span></div>
                                    <div class="col-md-3"><small class="text-muted d-block">Name</small><span class="fw-semibold"><?= e($student['guardian_name'] ?? '-') ?></span></div>
                                    <div class="col-md-3"><small class="text-muted d-block">Mobile</small><span><?= e($student['guardian_phone'] ?? '-') ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Tab -->
            <div class="tab-pane fade" id="tab-fees" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body p-0">
                        <?php if (empty($student['payments'])): ?>
                            <div class="text-center text-muted py-5">No payment history.</div>
                        <?php else: ?>
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Receipt</th><th>Amount</th><th>Mode</th><th>Date</th><th>Collected By</th></tr></thead>
                                <tbody>
                                    <?php foreach ($student['payments'] as $p): ?>
                                    <tr>
                                        <td><a href="<?= url('payments/'.$p['id'].'/receipt') ?>"><?= e($p['receipt_number']) ?></a></td>
                                        <td class="fw-semibold"><?= formatCurrency($p['amount']) ?></td>
                                        <td><?= ucfirst($p['payment_mode']) ?></td>
                                        <td><?= formatDate($p['payment_date']) ?></td>
                                        <td><small><?= e($p['collected_by_name']??'-') ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-header bg-white d-flex justify-content-end p-2 border-bottom">
                        <?php if (hasPermission('documents.upload')): ?><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>Upload Document</button><?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($student['documents'])): ?>
                        <div class="text-center text-muted py-5">No documents uploaded.</div>
                        <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Document</th><th>Type</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($student['documents'] as $doc): ?>
                                <tr>
                                    <td><a href="<?= url('documents/'.$doc['id'].'/download') ?>"><i class="fas fa-file-pdf text-danger me-2"></i><?= e($doc['file_name'] ?? $doc['title']) ?></a></td>
                                    <td><?= ucfirst(str_replace('_',' ',$doc['document_type'])) ?></td>
                                    <td><span class="badge bg-<?= ($doc['is_verified'] ?? 0) ? 'success' : 'warning' ?>"><?= ($doc['is_verified'] ?? 0) ? 'Verified' : 'Pending' ?></span></td>
                                    <td class="text-end">
                                        <a href="<?= url('documents/'.$doc['id'].'/download') ?>" class="btn btn-xs btn-outline-secondary" title="Download"><i class="fas fa-download"></i></a>
                                        <?php if (hasPermission('documents.delete')): ?>
                                        <button class="btn btn-xs btn-outline-danger btn-delete-doc ms-1" data-id="<?= $doc['id'] ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Timeline Tab -->
            <div class="tab-pane fade" id="tab-timeline" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-header bg-white d-flex justify-content-end p-2 border-bottom">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#noteModal"><i class="fas fa-plus me-1"></i> Add Observation</button>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php if (empty($student['activities'])): ?>
                                <p class="text-center text-muted py-3">No activity recorded yet.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($student['activities'], 0, 50) as $act): ?>
                                <?php $icon = match($act['type']) { 'behavioral_note'=>'fa-exclamation-triangle text-warning', 'fee_payment'=>'fa-money-bill text-success', 'attendance'=>'fa-calendar-check text-info', default=>'fa-circle text-primary' }; ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"><i class="fas <?= $icon ?> bg-white"></i></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <div class="fw-semibold"><?= e($act['title']) ?></div>
                                            <small class="text-muted"><?= timeAgo($act['created_at']) ?></small>
                                        </div>
                                        <?php if (!empty($act['description'])): ?>
                                            <div class="small text-muted mt-1 p-2 bg-light rounded border"><?= nl2br(e($act['description'])) ?></div>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mt-1">By <?= e($act['user_name'] ?? 'System') ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Tab -->
            <div class="tab-pane fade" id="tab-exams" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-signature fa-3x text-muted mb-3 d-block"></i>
                        <h6 class="text-muted">No exams mapped for this semester.</h6>
                    </div>
                </div>
            </div>

            <!-- Attendance Tab -->
            <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                <div class="card border-top-0 rounded-0 rounded-bottom mb-3">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3 d-block"></i>
                        <h6 class="text-muted">Attendance module syncing in progress...</h6>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="docUploadForm" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="entity_type" value="student">
                <input type="hidden" name="entity_id" value="<?= $student['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select class="form-select" name="document_type">
                            <?php foreach (['aadhar'=>'Aadhar Card','pan'=>'PAN Card','birth_certificate'=>'Birth Certificate','tc'=>'Transfer Certificate','marksheet'=>'Marksheet','photo'=>'Photo','other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Title (optional)</label><input type="text" class="form-control" name="title" placeholder="e.g. 10th Marksheet"></div>
                    <div class="mb-3"><label class="form-label">File</label><input type="file" class="form-control" name="document" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Note / Behavioral Observation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="noteForm">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Note Type</label>
                        <select class="form-select" name="type">
                            <option value="note">General Note</option>
                            <option value="behavioral_note">Behavioral Observation</option>
                            <option value="academic_note">Academic Note</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" name="note" rows="4" required placeholder="Enter note details here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Note</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('docUploadForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= url('documents/upload') ?>', {method:'POST', body:fd})
      .then(r=>r.json())
      .then(d=>{ if(d.success){location.reload();}else{alert(d.message);} });
});

document.getElementById('noteForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= url('students/'.$student['id'].'/note') ?>', {method:'POST', body:fd})
      .then(r=>r.json())
      .then(d=>{ if(d.success){location.reload();}else{alert(d.message);} });
});

document.querySelectorAll('.btn-delete-doc').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to delete this document?')) {
            const id = this.dataset.id;
            fetch('<?= url('documents/') ?>' + id + '/delete', {method:'POST', body: new FormData()})
              .then(r=>r.json())
              .then(d=>{ if(d.success){location.reload();}else{alert(d.message);} });
        }
    });
});
</script>
