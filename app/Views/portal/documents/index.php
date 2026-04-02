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
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-folder-open me-2 text-success"></i>Submitted Documents <span class="text-muted small fw-normal ms-2">(<?= count($documents) ?> files)</span></div>
    </div>

    <?php if (empty($documents)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-folder-open d-block fs-1 mb-3 opacity-25"></i>
        <div class="fw-semibold mb-1">No Documents Found</div>
        <div class="small">Documents submitted during your admission will appear here.</div>
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
                $hasFile = !empty($doc['file_path']);
                $verified = !empty($doc['is_verified']);
            ?>
            <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center gap-2 p-2 rounded-2 border" style="background:#f8fafc">
                    <div class="flex-shrink-0" style="color:<?= $color ?>"><i class="<?= $icon ?>"></i></div>
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="fw-semibold" style="font-size:0.8rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                            <?= e($doc['title'] ?? $doc['file_name'] ?? $typeLabel) ?>
                        </div>
                        <div class="d-flex gap-1 mt-1 align-items-center">
                            <?php if ($verified): ?>
                            <span class="badge bg-success-subtle text-success border" style="font-size:0.65rem"><i class="fas fa-check me-1"></i>Verified</span>
                            <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border" style="font-size:0.65rem">Pending</span>
                            <?php endif; ?>
                            <?php if (!empty($doc['created_at'])): ?>
                            <span class="text-muted" style="font-size:0.65rem"><?= date('d M Y', strtotime($doc['created_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>
