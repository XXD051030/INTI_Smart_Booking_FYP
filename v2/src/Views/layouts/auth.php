<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Access Portal') . ' | INTI Booking V2') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-hero">
            <div class="auth-hero__logo">
                <img src="<?= e(site_url('images/logo/inti_logo.png')) ?>" alt="INTI logo">
            </div>
            <div class="auth-hero__copy">
                <p class="eyebrow">INTI Smart Booking</p>
                <h1><?= e($authTitle ?? 'Secure campus reservations with less friction.') ?></h1>
                <p><?= e($authSubtitle ?? 'A focused student booking experience for rooms, labs, and sports facilities.') ?></p>
            </div>
            <ul class="auth-hero__list">
                <?php foreach (($authHighlights ?? []) as $item): ?>
                    <li><?= e($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section class="auth-panel">
            <?php include APP_ROOT . '/src/Views/partials/alerts.php'; ?>
            <?= $content ?>
        </section>
    </main>
    <script src="<?= e(asset_url('js/app.js')) ?>" defer></script>
</body>
</html>
