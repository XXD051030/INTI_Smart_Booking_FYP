<?php declare(strict_types=1); ?>
<section class="panel">
    <div class="calendar-toolbar">
        <div>
            <p class="eyebrow">Updates</p>
            <h2>Notifications</h2>
        </div>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button class="button button--outline" type="submit">Mark all as read</button>
        </form>
    </div>
    <p class="helper-text">Stationary in-app notifications are enabled now. Mail delivery is prepared in the backend but intentionally disabled.</p>
</section>

<section class="notification-list">
    <?php if ($notifications === []): ?>
        <div class="empty-state">
            <h3>No notifications yet</h3>
            <p>Booking confirmations and cancellations will appear here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
            <article class="notification-item <?= (int) $notification['is_read'] === 0 ? 'is-unread' : '' ?>">
                <div class="calendar-toolbar">
                    <div>
                        <h3><?= e($notification['title']) ?></h3>
                        <p><?= e($notification['message']) ?></p>
                    </div>
                    <span class="status-badge <?= (int) $notification['is_read'] === 0 ? 'is-confirmed' : 'is-complete' ?>">
                        <?= (int) $notification['is_read'] === 0 ? 'Unread' : 'Read' ?>
                    </span>
                </div>
                <div class="panel-footer">
                    <span class="helper-text"><?= e(time_ago($notification['created_at'])) ?></span>
                    <?php if ((int) $notification['is_read'] === 0): ?>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="notification_id" value="<?= e((string) $notification['id']) ?>">
                            <button class="button button--ghost" type="submit">Mark as read</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
