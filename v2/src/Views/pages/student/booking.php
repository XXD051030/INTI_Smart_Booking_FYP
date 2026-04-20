<?php declare(strict_types=1); ?>
<section class="split-layout" data-booking-panel data-max-slots="<?= e((string) $maxSlots) ?>">
    <div class="booking-sidebar">
        <article class="panel">
            <p class="eyebrow">Select facilities</p>
            <h2>Book facilities</h2>
            <div class="filter-pills">
                <?php foreach ($facilityTypes as $typeKey => $typeLabel): ?>
                    <a href="<?= e(app_url('booking.php?' . http_build_query(array_filter(['type' => $typeKey === 'all' ? null : $typeKey, 'facility' => $selectedFacility['id'] ?? null, 'date' => $selectedDate ?: null])))) ?>" class="<?= $activeType === $typeKey ? 'is-active' : '' ?>">
                        <?= e($typeLabel) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>

        <div class="booking-grid">
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
                            <span><?= (int) $facility['advance_booking_days'] === 0 ? 'Same day booking only' : 'Up to ' . e((string) $facility['advance_booking_days']) . ' day(s) in advance' ?></span>
                        </div>
                        <div class="panel-footer">
                            <a class="button <?= ($selectedFacility['id'] ?? null) === $facility['id'] ? 'button--primary' : 'button--outline' ?>" href="<?= e(app_url('booking.php?' . http_build_query(array_filter(['facility' => $facility['id'], 'type' => $activeType !== 'all' ? $activeType : null, 'date' => $selectedDate ?: null])))) ?>">
                                <?= ($selectedFacility['id'] ?? null) === $facility['id'] ? 'Selected' : 'Choose facility' ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="booking-stage">
        <?php if ($selectedFacility === null): ?>
            <div class="empty-state">
                <h3>Select a facility to start booking</h3>
                <p>Choose from the available facilities on the left to begin your reservation flow.</p>
            </div>
        <?php else: ?>
            <article class="surface">
                <p class="eyebrow">Booking</p>
                <h2><?= e($selectedFacility['name']) ?></h2>
                <div class="detail-grid">
                    <article class="detail-card">
                        <strong>Capacity</strong>
                        <p><?= e((string) $selectedFacility['capacity']) ?> people</p>
                    </article>
                    <article class="detail-card">
                        <strong>Location</strong>
                        <p><?= e($selectedFacility['location']) ?></p>
                    </article>
                    <article class="detail-card">
                        <strong>Operating hours</strong>
                        <p><?= e(format_time_range($selectedFacility['operating_start_time'], $selectedFacility['operating_end_time'])) ?></p>
                    </article>
                    <article class="detail-card">
                        <strong>Booking rule</strong>
                        <p><?= (int) $selectedFacility['advance_booking_days'] === 0 ? 'Same day booking only' : 'Up to ' . e((string) $selectedFacility['advance_booking_days']) . ' day(s) in advance' ?></p>
                    </article>
                </div>
            </article>

            <article class="panel">
                <form method="GET" class="stack-form">
                    <input type="hidden" name="facility" value="<?= e((string) $selectedFacility['id']) ?>">
                    <?php if ($activeType !== 'all'): ?>
                        <input type="hidden" name="type" value="<?= e($activeType) ?>">
                    <?php endif; ?>
                    <div class="form-field">
                        <label for="date">Select date</label>
                        <input type="date" id="date" name="date" value="<?= e($selectedDate) ?>" min="<?= e($dateBounds['min']) ?>" max="<?= e($dateBounds['max']) ?>" required>
                    </div>
                    <button class="button button--outline" type="submit">Load availability</button>
                </form>
            </article>

            <?php if ($selectedDate !== ''): ?>
                <article class="panel">
                    <div class="calendar-toolbar">
                        <div>
                            <p class="eyebrow">Availability</p>
                            <h2><?= e(format_long_date($selectedDate)) ?></h2>
                        </div>
                        <span class="status-badge is-complete">Daily requests used: <?= e((string) $dailyRequestCount) ?>/<?= e((string) $dailyLimit) ?></span>
                    </div>

                    <?php if ($availability === []): ?>
                        <div class="empty-state">
                            <h3>No time slots to show</h3>
                            <p>Select a valid date within the facility booking window.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="stack-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create_booking">
                            <input type="hidden" name="facility_id" value="<?= e((string) $selectedFacility['id']) ?>">
                            <input type="hidden" name="booking_date" value="<?= e($selectedDate) ?>">
                            <div class="slot-grid">
                                <?php foreach ($availability as $slot): ?>
                                    <label class="slot-pill <?= $slot['available'] ? '' : 'is-disabled' ?>">
                                        <input
                                            type="checkbox"
                                            name="slots[]"
                                            value="<?= e($slot['start_time']) ?>"
                                            data-slot-input
                                            <?= $slot['available'] ? '' : 'disabled' ?>
                                        >
                                        <span><?= e(format_time_range($slot['start_time'], $slot['end_time'])) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="form-help" data-slot-warning hidden>Selected slots must stay consecutive.</p>
                            <div class="form-field">
                                <label for="purpose">Purpose</label>
                                <textarea id="purpose" name="purpose" placeholder="Describe the purpose of this booking." required><?= e((string) old('purpose')) ?></textarea>
                            </div>
                            <div class="inline-actions">
                                <button class="button button--primary" type="submit">Confirm booking</button>
                                <a class="button button--ghost" href="<?= e(app_url('my_bookings.php')) ?>">View my bookings</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
