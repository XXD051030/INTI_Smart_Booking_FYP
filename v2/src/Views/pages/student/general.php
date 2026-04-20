<?php declare(strict_types=1); ?>
<section class="hero-split">
    <article class="hero-block">
        <p class="eyebrow">Reservation dashboard</p>
        <h2><?= e($currentUser['display_name']) ?></h2>
        <p>Your booking workspace keeps rooms, labs, courts, and notifications in one focused flow.</p>
        <div class="status-strip">
            <span class="status-badge is-complete">Student ID: <?= e(student_id_from_email($currentUser['email'])) ?></span>
            <span class="status-badge is-confirmed"><?= e((string) $notificationCount) ?> unread notifications</span>
        </div>
    </article>
    <article class="surface">
        <h2>Quick status</h2>
        <div class="detail-list">
            <div><strong>Email</strong><span><?= e($currentUser['email']) ?></span></div>
            <div><strong>Language</strong><span><?= e(strtoupper($currentUser['preferred_language'])) ?></span></div>
            <div><strong>Upcoming bookings</strong><span><?= e((string) $stats['upcoming']) ?></span></div>
            <div><strong>Total requests</strong><span><?= e((string) $stats['total']) ?></span></div>
        </div>
    </article>
</section>

<section class="stats-grid">
    <article>
        <p>Total bookings</p>
        <strong><?= e((string) $stats['total']) ?></strong>
    </article>
    <article>
        <p>Confirmed</p>
        <strong><?= e((string) $stats['confirmed']) ?></strong>
    </article>
    <article>
        <p>Completed</p>
        <strong><?= e((string) $stats['completed']) ?></strong>
    </article>
    <article>
        <p>Cancelled</p>
        <strong><?= e((string) $stats['cancelled']) ?></strong>
    </article>
</section>

<section class="panel">
    <div class="calendar-toolbar">
        <div>
            <p class="eyebrow">Book by venue</p>
            <h2>Choose your facility</h2>
        </div>
        <a class="button button--primary" href="<?= e(app_url('booking.php')) ?>">Open booking workspace</a>
    </div>
    <div class="facility-grid">
        <?php foreach ($facilities as $facility): ?>
            <article class="facility-card">
                <div class="facility-card__image">
                    <img src="<?= e(site_url(ltrim($facility['image_path'], '/'))) ?>" alt="<?= e($facility['name']) ?>">
                </div>
                <div class="facility-card__body">
                    <h3><?= e($facility['name']) ?></h3>
                    <div class="facility-card__meta">
                        <span>Capacity: <?= e((string) $facility['capacity']) ?></span>
                        <span><?= e($facility['location']) ?></span>
                        <span>Advance rule: <?= e((string) $facility['advance_booking_days']) ?> day(s)</span>
                    </div>
                    <div class="panel-footer">
                        <a class="button button--outline" href="<?= e(app_url('booking.php?facility=' . $facility['id'])) ?>">Book now</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
