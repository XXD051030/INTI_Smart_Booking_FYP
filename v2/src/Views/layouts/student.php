<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Dashboard') . ' | INTI Booking V2') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body class="app-body">
    <div class="app-shell">
        <?php include APP_ROOT . '/src/Views/partials/student_sidebar.php'; ?>
        <div class="app-main">
            <?php include APP_ROOT . '/src/Views/partials/student_topbar.php'; ?>
            <main class="app-content">
                <?php include APP_ROOT . '/src/Views/partials/alerts.php'; ?>
                <?= $content ?>
            </main>
        </div>
    </div>
    <script src="<?= e(asset_url('js/app.js')) ?>" defer></script>
    <?php foreach (($pageScripts ?? []) as $script): ?>
        <script src="<?= e(asset_url('js/' . $script)) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
