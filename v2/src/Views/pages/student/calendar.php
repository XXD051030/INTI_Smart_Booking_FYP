<?php declare(strict_types=1); ?>
<section class="stats-grid">
    <article>
        <p>Total bookings</p>
        <strong><?= e((string) $stats['total']) ?></strong>
    </article>
    <article>
        <p>This month</p>
        <strong><?= e((string) $stats['month']) ?></strong>
    </article>
    <article>
        <p>Upcoming</p>
        <strong><?= e((string) $stats['upcoming']) ?></strong>
    </article>
    <article>
        <p>This week</p>
        <strong><?= e((string) $stats['week']) ?></strong>
    </article>
</section>

<section class="calendar-panel">
    <div class="calendar-toolbar">
        <div class="calendar-headline">
            <div>
                <p class="eyebrow">Calendar overview</p>
                <h2><?= e($monthLabel) ?></h2>
            </div>
        </div>
        <div class="inline-actions">
            <a class="button button--ghost" href="<?= e(app_url('calendar.php?month=' . $previousMonth)) ?>">Previous</a>
            <a class="button button--outline" href="<?= e(app_url('calendar.php')) ?>">Today</a>
            <a class="button button--ghost" href="<?= e(app_url('calendar.php?month=' . $nextMonth)) ?>">Next</a>
        </div>
    </div>

    <div class="calendar-grid">
        <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday): ?>
            <div class="calendar-weekday"><?= e($weekday) ?></div>
        <?php endforeach; ?>

        <?php foreach ($calendarDays as $day): ?>
            <div class="calendar-day <?= !$day['currentMonth'] ? 'is-outside' : '' ?> <?= $day['isToday'] ? 'is-today' : '' ?>">
                <strong><?= e($day['day']) ?></strong>
                <?php foreach ($day['events'] as $event): ?>
                    <article class="calendar-event">
                        <span><?= e($event['facility_name']) ?></span>
                        <small><?= e(format_time_range($event['start_time'], $event['end_time'])) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
