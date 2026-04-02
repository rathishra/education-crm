<?php
$docTypeLabels = [
    'marksheet'             => ['fas fa-file-alt',          '#3b82f6',  'Marksheet'],
    'transfer_certificate'  => ['fas fa-certificate',        '#059669',  'Transfer Certificate'],
    'conduct_certificate'   => ['fas fa-award',              '#7c3aed',  'Conduct Certificate'],
    'id_proof'              => ['fas fa-id-card',            '#d97706',  'ID Proof'],
    'community_certificate' => ['fas fa-users',              '#0891b2',  'Community Certificate'],
    'income_certificate'    => ['fas fa-rupee-sign',         '#059669',  'Income Certificate'],
    'passport_photo'        => ['fas fa-camera',             '#db2777',  'Passport Photo'],
    'medical_record'        => ['fas fa-heartbeat',          '#dc2626',  'Medical Record'],
    'fee_receipt'           => ['fas fa-receipt',            '#d97706',  'Fee Receipt'],
    'other'                 => ['fas fa-paperclip',          '#64748b',  'Other'],
];
function docTypeInfo(string $type, array $map): array {
    return $map[strtolower($type)] ?? ['fas fa-file', '#64748b', ucwords(str_replace('_', ' ', $type))];
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-folder-open me-2 text-success"></i>My Documents</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Documents</div>
    </div>
    <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fas fa-upload me-2"></i>Upload Document
    </button>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <?php if ($admission && in_array($admission['status'] ?? '', ['confirmed', 'enrolled'])): ?>
    <div class="col-sm-6 col-lg-4">
        <a href="<?= url('admissions/' . $admission['id'] . '/admission-letter') ?>" target="_blank" class="portal-stat-card text-decoration-none" style="color:inherit">
            <div class="portal-stat-icon" style="background:#d1fae5;color:#065f46"><i class="fas fa-certificate"></i></div>
            <div>
                <div class="portal-stat-label">Admission Letter</div>
                <div class="fw-semibold small text-success"><?= e($admission['admission_number'] ?? '') ?></div>
                <div class="text-muted" style="font-size:0.72rem">Click to download / print</div>
            </div>
        </a>
    </div>
    <?php endif; ?>
    <?php if ($admission): ?>
    <div class="col-sm-6 col-lg-4">
        <a href="<?= url('admissions/' . $admission['id'] . '/offer-letter') ?>" target="_blank" class="portal-stat-card text-decoration-none" style="color:inherit">
            <div class="portal-stat-icon" style="background:#dbeafe;color:#1e40af"><i class="fas fa-envelope-open-text"></i></div>
            <div>
                <div class="portal-stat-label">Offer Letter</div>
                <div class="fw-semibold small text-primary"><?= e($admission['admission_number'] ?? '') ?></div>
                <div class="text-muted" style="font-size:0.72rem">Click to download / print</div>
            </div>
        </a>
    </div>
    <?php endif; ?>
    <div class="col-sm-6 col-lg-4">
        <a href="<?= url('portal/student/fees') ?>" class="portal-stat-card text-decoration-none" style="color:inherit">
            <div class="portal-stat-icon" style="background:#fef3c7;color:#92400e"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="portal-stat-label">Fee Receipts</div>
                <div class="fw-semibold small text-warning">View All Receipts</div>
                <div class="text-muted" style="font-size:0.72rem">Fees & Payments section</div>
            </div>
        </a>
    </div>
</div>

<!-- Submitted Documents -->
<div class="portal-card">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2 d-flex align-items-center justify-content-between">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-folder-open me-2 text-success"></i>My Documents <span class="text-muted small fw-normal ms-2">(<?= count($documents) ?> files)</span></div>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus me-1"></i>Upload
        </button>
    </div>

    <?php if (empty($documents)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-folder-open d-block fs-1 mb-3 opacity-25"></i>
        <div class="fw-semibold mb-1">No Documents Yet</div>
        <div class="small mb-3">Upload your certificates, ID proofs, and other documents here.</div>
        <button class="btn btn-success btn-sm px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload me-2"></i>Upload Your First Document
        </button>
    </div>
    <?php else: ?>

    <?php foreach ($byType as $docType => $docs):
        [$icon, $color, $typeLabel] = docTypeInfo($docType, $docTypeLabels);
    ?>
    <div class="px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="<?= $icon ?>" style="color:<?= $color ?>"></i>
            <div class="fw-semibold" style="font-size:0.875rem"><?= $typeLabel ?></div>
            <span class="badge ms-1" style="background:<?= $color ?>18;color:<?= $color ?>;font-size:0.68rem"><?= count($docs) ?></span>
        </div>
        <div class="row g-2">
            <?php foreach ($docs as $doc):
                $hasFile  = !empty($doc['file_path']);
                $verified = !empty($doc['is_verified']);
                $canDelete = ($doc['source'] ?? '') === 'student_doc';
            ?>
            <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="background:#f8fafc">
                    <div class="flex-shrink-0" style="color:<?= $color ?>"><i class="<?= $icon ?>"></i></div>
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="fw-semibold" style="font-size:0.8rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis" title="<?= e($doc['title'] ?? $doc['file_name'] ?? $typeLabel) ?>">
                            <?= e($doc['title'] ?? $doc['file_name'] ?? $typeLabel) ?>
                        </div>
                        <div class="d-flex gap-1 mt-1 align-items-center flex-wrap">
                            <?php if ($verified): ?>
                            <span class="badge bg-success-subtle text-success border" style="font-size:0.62rem"><i class="fas fa-check me-1"></i>Verified</span>
                            <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border" style="font-size:0.62rem">Pending</span>
                            <?php endif; ?>
                            <?php if (!empty($doc['file_size'])): ?>
                            <span class="text-muted" style="font-size:0.62rem"><?= round($doc['file_size'] / 1024, 0) ?> KB</span>
                            <?php endif; ?>
                            <?php if (!empty($doc['created_at'])): ?>
                            <span class="text-muted" style="font-size:0.62rem"><?= date('d M Y', strtotime($doc['created_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 flex-shrink-0">
                        <?php if ($hasFile && $canDelete): ?>
                        <a href="<?= url('portal/student/documents/' . $doc['id'] . '/download') ?>"
                           class="btn btn-outline-primary" style="padding:2px 7px;font-size:0.7rem" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <form method="POST" action="<?= url('portal/student/documents/' . $doc['id'] . '/delete') ?>"
                              onsubmit="return confirm('Remove this document?')">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-outline-danger" style="padding:2px 7px;font-size:0.7rem" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php elseif ($hasFile): ?>
                        <a href="<?= url('portal/student/documents/' . $doc['id'] . '/download') ?>"
                           class="btn btn-outline-primary" style="padding:2px 7px;font-size:0.7rem" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<!-- ── Upload Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 fw-bold"><i class="fas fa-upload me-2 text-success"></i>Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('portal/student/documents/upload') ?>"
                  enctype="multipart/form-data" id="uploadForm">
                <?= csrfField() ?>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select form-select-sm" required>
                            <option value="">— Select type —</option>
                            <option value="marksheet">Marksheet</option>
                            <option value="transfer_certificate">Transfer Certificate</option>
                            <option value="conduct_certificate">Conduct Certificate</option>
                            <option value="id_proof">ID Proof (Aadhar / PAN / Passport)</option>
                            <option value="community_certificate">Community Certificate</option>
                            <option value="income_certificate">Income Certificate</option>
                            <option value="passport_photo">Passport Photo</option>
                            <option value="medical_record">Medical Record</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title / Label</label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               placeholder="e.g. 10th Grade Marksheet, Aadhar Card">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">File <span class="text-danger">*</span></label>
                        <div class="upload-drop-area rounded-3 border-2 border-dashed p-4 text-center"
                             style="border:2px dashed #bbf7d0;background:#f0fdf4;cursor:pointer"
                             id="dropArea" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt fs-3 text-success mb-2 d-block"></i>
                            <div class="fw-semibold small text-success">Click to browse or drag & drop</div>
                            <div class="text-muted" style="font-size:0.75rem">PDF, JPG, PNG, DOC, DOCX — max 5 MB</div>
                            <div id="fileName" class="mt-2 fw-semibold small text-success d-none"></div>
                        </div>
                        <input type="file" id="fileInput" name="document" class="d-none"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required
                               onchange="showFileName(this)">
                    </div>

                    <div class="p-2 rounded-2 bg-warning-subtle small text-warning-emphasis">
                        <i class="fas fa-info-circle me-1"></i>
                        Uploaded documents will be reviewed and verified by the administration.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success px-4" id="uploadBtn">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const label = document.getElementById('fileName');
    const area  = document.getElementById('dropArea');
    if (input.files && input.files[0]) {
        label.textContent = input.files[0].name;
        label.classList.remove('d-none');
        area.style.borderColor = '#059669';
        area.style.background  = '#ecfdf5';
    }
}

// Drag & drop
const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('fileInput');
['dragover','dragenter'].forEach(ev => {
    dropArea.addEventListener(ev, e => { e.preventDefault(); dropArea.style.borderColor = '#059669'; });
});
dropArea.addEventListener('dragleave', () => { dropArea.style.borderColor = '#bbf7d0'; });
dropArea.addEventListener('drop', e => {
    e.preventDefault();
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileName(fileInput);
    }
});

// Loading state on submit
document.getElementById('uploadForm').addEventListener('submit', function () {
    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading…';
});
</script>
