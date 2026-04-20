<?php declare(strict_types=1); ?>
<article class="auth-card">
    <p class="eyebrow">Admin access</p>
    <h2>Sign in to admin</h2>
    <p class="auth-card__meta">Manage students, booking status, exports, and password resets from the V2 console.</p>

    <form method="POST" class="auth-form">
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" placeholder="admin" value="<?= e((string) old('username')) ?>" required>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter admin password" required>
        </div>
        <button class="button button--primary" type="submit">Enter admin console</button>
    </form>

    <p class="auth-switch"><a href="<?= e(app_url('login.php')) ?>">Back to student sign in</a></p>
</article>
