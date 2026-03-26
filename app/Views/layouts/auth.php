<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Login') ?> - <?= e(config('app.name', 'Education CRM')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center min-vh-100 align-items-center">
                        <div class="col-lg-5 col-md-7 col-sm-9">
                            <?php
                            $flashError = getFlash('error');
                            $flashSuccess = getFlash('success');
                            ?>
                            <?php if ($flashError): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= e($flashError) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($flashSuccess): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= e($flashSuccess) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?= $content ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
