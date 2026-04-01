<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Print') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-family: 'Segoe UI', Arial, sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .page-break { page-break-before: always; }
        }
        @media screen {
            body { background: #f0f0f0; }
            .print-page { background: #fff; max-width: 210mm; margin: 20px auto; box-shadow: 0 0 20px rgba(0,0,0,.15); }
        }
    </style>
    <?php if (!empty($extraCss)): foreach ((array)$extraCss as $css): ?>
        <link href="<?= $css ?>" rel="stylesheet">
    <?php endforeach; endif; ?>
</head>
<body>

<!-- Print Controls -->
<div class="no-print bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between" style="position:sticky;top:0;z-index:999">
    <span class="small"><i class="fas fa-print me-1"></i><?= e($pageTitle ?? 'Print Preview') ?></span>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-warning"><i class="fas fa-print me-1"></i>Print</button>
        <button onclick="window.close()" class="btn btn-sm btn-outline-light"><i class="fas fa-times me-1"></i>Close</button>
    </div>
</div>

<?= $content ?? '' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): foreach ((array)$extraJs as $js): ?>
    <script src="<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
