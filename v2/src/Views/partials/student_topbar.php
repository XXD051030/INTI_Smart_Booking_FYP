<?php declare(strict_types=1); ?>
<header class="topbar">
    <button type="button" class="topbar__menu" data-sidebar-toggle aria-label="Toggle navigation">Menu</button>
    <div>
        <p class="topbar__kicker">Student portal</p>
        <h1><?= e($pageHeading ?? 'Dashboard') ?></h1>
    </div>
    <div class="topbar__actions">
        <a href="<?= e(app_url('notifications.php')) ?>" class="notification-pill">
            <span>Notifications</span>
            <?php if (($notificationCount ?? 0) > 0): ?>
                <strong><?= e((string) $notificationCount) ?></strong>
            <?php endif; ?>
        </a>
        <div class="topbar__identity">
            <span><?= e($currentUser['display_name'] ?? 'Student') ?></span>
            <a href="<?= e(app_url('logout.php')) ?>">Logout</a>
        </div>
    </div>
</header>
