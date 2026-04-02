<?php
$typeConfig = [
    'info'    => ['fas fa-info-circle',           '#3b82f6', '#dbeafe', 'Info'],
    'success' => ['fas fa-check-circle',           '#059669', '#d1fae5', 'Success'],
    'warning' => ['fas fa-exclamation-triangle',   '#d97706', '#fef3c7', 'Warning'],
    'error'   => ['fas fa-times-circle',           '#dc2626', '#fee2e2', 'Alert'],
    'notice'  => ['fas fa-bullhorn',               '#7c3aed', '#ede9fe', 'Notice'],
    'exam'    => ['fas fa-file-alt',               '#0891b2', '#e0f2fe', 'Exam'],
    'fee'     => ['fas fa-rupee-sign',             '#d97706', '#fef3c7', 'Fee'],
    'general' => ['fas fa-bell',                   '#475569', '#f1f5f9', 'General'],
];
function notifConfig(string $type, array $map): array {
    return $map[strtolower($type)] ?? $map['general'];
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-bell me-2 text-success"></i>Notifications</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Notifications</div>
    </div>
    <div class="text-muted small"><?= count($notifications) ?> notifications</div>
</div>

<div class="portal-card">
    <?php if (empty($notifications)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-bell-slash d-block fs-1 mb-3 opacity-25"></i>
        <div class="fw-semibold mb-1">No Notifications</div>
        <div class="small">You're all caught up! New announcements will appear here.</div>
    </div>
    <?php else: ?>
    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $notif):
            [$icon, $color, $bg, $label] = notifConfig($notif['type'] ?? 'general', $typeConfig);
            $isNew = !$notif['is_read'];
        ?>
        <div class="list-group-item px-4 py-3 border-0 border-bottom <?= $isNew ? '' : '' ?>"
             style="<?= $isNew ? 'background:#f0fdf4' : '' ?>">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 mt-1"
                     style="width:38px;height:38px;background:<?= $bg ?>">
                    <i class="<?= $icon ?>" style="color:<?= $color ?>;font-size:0.9rem"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="fw-semibold <?= $isNew ? '' : 'text-muted' ?>" style="font-size:0.9rem"><?= e($notif['title']) ?></div>
                        <div class="d-flex gap-2 align-items-center flex-shrink-0">
                            <span class="badge px-2 py-1" style="background:<?= $bg ?>;color:<?= $color ?>;font-size:0.68rem"><?= $label ?></span>
                            <?php if ($isNew): ?>
                            <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($notif['message'])): ?>
                    <div class="text-muted mt-1" style="font-size:0.82rem;line-height:1.5"><?= nl2br(e($notif['message'])) ?></div>
                    <?php endif; ?>
                    <div class="text-muted mt-1" style="font-size:0.72rem">
                        <i class="fas fa-clock me-1"></i>
                        <?php
                        $ts   = strtotime($notif['created_at']);
                        $diff = time() - $ts;
                        if ($diff < 60)          echo 'Just now';
                        elseif ($diff < 3600)    echo floor($diff/60) . ' min ago';
                        elseif ($diff < 86400)   echo floor($diff/3600) . ' hr ago';
                        elseif ($diff < 604800)  echo floor($diff/86400) . ' days ago';
                        else                     echo date('d M Y', $ts);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
