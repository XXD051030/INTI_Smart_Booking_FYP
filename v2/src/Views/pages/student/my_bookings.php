<?php declare(strict_types=1); ?>
<section class="stats-grid">
    <article>
        <p>Total bookings</p>
        <strong><?= e((string) $stats['total']) ?></strong>
    </article>
    <article>
        <p>Completed</p>
        <strong><?= e((string) $stats['completed']) ?></strong>
    </article>
    <article>
        <p>Cancelled</p>
        <strong><?= e((string) $stats['cancelled']) ?></strong>
    </article>
    <article>
        <p>Upcoming</p>
        <strong><?= e((string) $stats['upcoming']) ?></strong>
    </article>
</section>

<section class="panel">
    <div class="calendar-toolbar">
        <div>
            <p class="eyebrow">Booking history</p>
            <h2>Your bookings</h2>
        </div>
        <div class="filter-pills">
            <?php foreach ($filters as $filterKey => $filterLabel): ?>
                <a href="<?= e(app_url('my_bookings.php' . ($filterKey === 'all' ? '' : '?scope=' . $filterKey))) ?>" class="<?= $activeFilter === $filterKey ? 'is-active' : '' ?>">
                    <?= e($filterLabel) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="booking-grid">
    <?php if ($bookings === []): ?>
        <div class="empty-state">
            <h3>No bookings in this view</h3>
            <p>Switch filters or create a new request from the booking workspace.</p>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <article class="booking-card">
                <header class="booking-card__header">
                    <div>
                        <p class="eyebrow"><?= e(format_booking_code((int) $booking['booking_id'])) ?></p>
                        <h3><?= e($booking['facility_name']) ?></h3>
                    </div>
                    <span class="status-badge <?= e(booking_status_class($booking)) ?>"><?= e(booking_display_status($booking)) ?></span>
                </header>
                <div class="booking-card__body">
                    <div class="booking-card__meta">
                        <span>Date: <?= e(format_long_date($booking['booking_date'])) ?></span>
                        <span>Time: <?= e(format_time_range($booking['start_time'], $booking['end_time'])) ?></span>
                        <span>Location: <?= e($booking['location']) ?></span>
                        <span>Purpose: <?= e($booking['purpose']) ?></span>
                    </div>
                    <div class="panel-footer">
                        <span class="helper-text">Created <?= e(time_ago($booking['created_at'])) ?></span>
                        <?php if (!empty($booking['can_cancel'])): ?>
                            <form method="POST" data-confirm="Cancel this booking request?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="cancel_booking">
                                <input type="hidden" name="request_token" value="<?= e($booking['request_token']) ?>">
                                <button type="submit" class="button button--outline">Cancel booking</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
