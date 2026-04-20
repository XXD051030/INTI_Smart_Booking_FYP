<?php declare(strict_types=1); ?>
<header class="topbar admin-topbar">
    <button type="button" class="topbar__menu" data-sidebar-toggle aria-label="Toggle navigation">Menu</button>
    <div>
        <p class="topbar__kicker">Admin portal</p>
        <h1><?= e($pageHeading ?? 'Dashboard') ?></h1>
    </div>
    <div class="topbar__actions">
        <div class="topbar__identity">
            <span><?= e($currentAdmin['display_name'] ?? 'Admin') ?></span>
            <a href="<?= e(admin_url('logout.php')) ?>">Logout</a>
        </div>
    </div>
</header>
