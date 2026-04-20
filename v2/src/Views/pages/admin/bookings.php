<?php declare(strict_types=1); ?>
<section class="panel">
    <div class="calendar-toolbar">
        <div>
            <p class="eyebrow">Booking status</p>
            <h2>Manage reservations</h2>
        </div>
        <a class="button button--outline" href="<?= e(action_url('export_bookings.php?' . http_build_query(array_filter($filters)))) ?>">Export bookings CSV</a>
    </div>
    <form method="GET" class="filter-bar">
        <div class="form-field">
            <label for="date">Date</label>
            <input id="date" name="date" type="date" value="<?= e($filters['date']) ?>">
        </div>
        <div class="form-field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <option value="confirmed" <?= $filters['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="form-field">
            <label for="facility_id">Facility</label>
            <select id="facility_id" name="facility_id">
                <option value="">All facilities</option>
                <?php foreach ($facilities as $facility): ?>
                    <option value="<?= e((string) $facility['id']) ?>" <?= (string) $filters['facility_id'] === (string) $facility['id'] ? 'selected' : '' ?>><?= e($facility['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label for="search">Search</label>
            <input id="search" name="search" type="text" value="<?= e($filters['search']) ?>" placeholder="Name, email, facility">
        </div>
        <div class="form-field">
            <label>&nbsp;</label>
            <button class="button button--primary" type="submit">Apply filters</button>
        </div>
    </form>
</section>

<?php if ($selectedBooking !== null): ?>
    <section class="detail-card">
        <h3><?= e(format_booking_code((int) $selectedBooking['booking_id'])) ?> details</h3>
        <div class="detail-grid">
            <article class="detail-card">
                <strong>Student</strong>
                <p><?= e($selectedBooking['display_name']) ?></p>
            </article>
            <article class="detail-card">
                <strong>Email</strong>
                <p><?= e($selectedBooking['email']) ?></p>
            </article>
            <article class="detail-card">
                <strong>Facility</strong>
                <p><?= e($selectedBooking['facility_name']) ?></p>
            </article>
            <article class="detail-card">
                <strong>Date & time</strong>
                <p><?= e(format_long_date($selectedBooking['booking_date'])) ?><br><?= e(format_time_range($selectedBooking['start_time'], $selectedBooking['end_time'])) ?></p>
            </article>
            <article class="detail-card">
                <strong>Status</strong>
                <p><?= e(booking_display_status($selectedBooking)) ?></p>
            </article>
            <article class="detail-card">
                <strong>Purpose</strong>
                <p><?= e($selectedBooking['purpose']) ?></p>
            </article>
        </div>
    </section>
<?php endif; ?>

<section class="table-panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Facility</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings === []): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <h3>No booking requests found</h3>
                                <p>Adjust the filters or wait for new reservations.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= e(format_booking_code((int) $booking['booking_id'])) ?></td>
                            <td><?= e($booking['display_name']) ?></td>
                            <td><?= e($booking['email']) ?></td>
                            <td><?= e($booking['facility_name']) ?></td>
                            <td><?= e(format_long_date($booking['booking_date'])) ?><br><span class="helper-text"><?= e(format_time_range($booking['start_time'], $booking['end_time'])) ?></span></td>
                            <td><span class="status-badge <?= e(booking_status_class($booking)) ?>"><?= e(booking_display_status($booking)) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="button button--ghost" href="<?= e(admin_url('bookings.php?' . http_build_query(array_filter(array_merge($filters, ['request' => $booking['request_token']]))))) ?>">View</a>
                                    <?php if (($booking['status'] ?? '') === 'confirmed'): ?>
                                        <form method="POST" data-confirm="Cancel this booking request from the admin console?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cancel_booking">
                                            <input type="hidden" name="request_token" value="<?= e($booking['request_token']) ?>">
                                            <button class="button button--outline" type="submit">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
