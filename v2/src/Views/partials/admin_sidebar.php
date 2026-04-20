<?php declare(strict_types=1);

$adminNav = [
    'dashboard' => ['label' => 'Dashboard', 'href' => admin_url('dashboard.php')],
    'bookings' => ['label' => 'Booking Status', 'href' => admin_url('bookings.php')],
];
?>
<aside class="sidebar admin-sidebar">
    <div class="sidebar__brand">
        <img src="<?= e(site_url('images/logo/inti_logo.png')) ?>" alt="INTI logo">
        <div>
            <p>INTI</p>
            <strong>Admin Console</strong>
        </div>
    </div>

    <nav class="sidebar__nav" aria-label="Admin navigation">
        <?php foreach ($adminNav as $navKey => $navItem): ?>
            <a href="<?= e($navItem['href']) ?>" class="sidebar__link <?= ($activeNav ?? '') === $navKey ? 'is-active' : '' ?>">
                <span><?= e($navItem['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__footer">
        <p>Environment</p>
        <strong>SQLite / V2</strong>
    </div>
</aside>
