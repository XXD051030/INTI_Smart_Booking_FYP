<?php declare(strict_types=1); ?>
<section class="stats-grid">
    <article>
        <p>Total users</p>
        <strong><?= e((string) $stats['users']) ?></strong>
    </article>
    <article>
        <p>Total bookings</p>
        <strong><?= e((string) $stats['bookings']) ?></strong>
    </article>
    <article>
        <p>Confirmed</p>
        <strong><?= e((string) $stats['confirmed']) ?></strong>
    </article>
    <article>
        <p>Active facilities</p>
        <strong><?= e((string) $stats['facilities']) ?></strong>
    </article>
</section>

<section class="panel">
    <div class="calendar-toolbar">
        <div>
            <p class="eyebrow">Admin actions</p>
            <h2>User management</h2>
        </div>
        <a class="button button--outline" href="<?= e(action_url('export_users.php' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>">Export users CSV</a>
    </div>
    <form method="GET" class="filter-bar">
        <div class="form-field">
            <label for="search">Search students</label>
            <input id="search" name="search" type="text" value="<?= e($search) ?>" placeholder="Search by name or email">
        </div>
        <div class="form-field">
            <label>&nbsp;</label>
            <button class="button button--primary" type="submit">Apply filter</button>
        </div>
    </form>
    <?php if ($editingUser !== null): ?>
        <section class="detail-card">
            <h3>Edit user</h3>
            <form method="POST" class="stack-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" value="<?= e((string) $editingUser['id']) ?>">
                <div class="form-grid form-grid--two">
                    <div class="form-field">
                        <label for="edit_display_name">Display name</label>
                        <input id="edit_display_name" name="display_name" type="text" value="<?= e($editingUser['display_name']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_email">Email</label>
                        <input id="edit_email" name="email" type="email" value="<?= e($editingUser['email']) ?>" required>
                    </div>
                </div>
                <div class="inline-actions">
                    <button class="button button--primary" type="submit">Save changes</button>
                    <a class="button button--ghost" href="<?= e(admin_url('dashboard.php' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>">Close editor</a>
                </div>
            </form>
        </section>
    <?php endif; ?>
</section>

<section class="table-panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Language</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users === []): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <h3>No users found</h3>
                                <p>Adjust the search query or register a new student in V2.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?= e($user['display_name']) ?></strong>
                            </td>
                            <td><?= e(student_id_from_email($user['email'])) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e(strtoupper($user['preferred_language'])) ?></td>
                            <td><?= e(format_timestamp_human($user['created_at'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="button button--ghost" href="<?= e(admin_url('dashboard.php?edit=' . $user['id'] . ($search !== '' ? '&search=' . urlencode($search) : ''))) ?>">Edit</a>
                                    <button type="button" class="button button--outline" data-toggle-target="#reset-<?= e((string) $user['id']) ?>">Reset password</button>
                                    <form method="POST" data-confirm="Delete this user and related bookings?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                        <button class="button button--outline" type="submit">Delete</button>
                                    </form>
                                </div>
                                <div id="reset-<?= e((string) $user['id']) ?>" hidden>
                                    <form method="POST" class="inline-password">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                        <div class="form-field">
                                            <label class="sr-only" for="password-<?= e((string) $user['id']) ?>">New password</label>
                                            <input id="password-<?= e((string) $user['id']) ?>" name="new_password" type="password" placeholder="New password" required>
                                        </div>
                                        <button class="button button--primary" type="submit">Save</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
