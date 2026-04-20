<?php declare(strict_types=1);

$studentNav = [
    'general' => ['label' => 'General', 'href' => app_url('general.php')],
    'booking' => ['label' => 'Booking', 'href' => app_url('booking.php')],
    'calendar' => ['label' => 'Calendar', 'href' => app_url('calendar.php')],
    'my_bookings' => ['label' => 'My Bookings', 'href' => app_url('my_bookings.php')],
    'settings' => ['label' => 'Settings', 'href' => app_url('settings.php')],
    'rules' => ['label' => 'Rules & Regulations', 'href' => app_url('rules.php')],
];
?>
<aside class="sidebar">
    <div class="sidebar__brand">
        <img src="<?= e(site_url('images/logo/inti_logo.png')) ?>" alt="INTI logo">
        <div>
            <p>INTI</p>
            <strong>Smart Booking</strong>
        </div>
    </div>

    <nav class="sidebar__nav" aria-label="Student navigation">
        <?php foreach ($studentNav as $navKey => $navItem): ?>
            <a href="<?= e($navItem['href']) ?>" class="sidebar__link <?= ($activeNav ?? '') === $navKey ? 'is-active' : '' ?>">
                <span><?= e($navItem['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__footer">
        <p>Student ID</p>
        <strong><?= e(student_id_from_email($currentUser['email'] ?? '')) ?></strong>
    </div>
</aside>
