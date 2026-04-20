<?php declare(strict_types=1); ?>
<article class="auth-card">
    <p class="eyebrow">Welcome back</p>
    <h2>Sign in</h2>
    <p class="auth-card__meta">Access your student dashboard, live calendar, and current reservations.</p>

    <form method="POST" class="auth-form">
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" placeholder="p23012345@student.newinti.edu.my" value="<?= e((string) old('email')) ?>" required>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter your password" required>
        </div>
        <button class="button button--primary" type="submit">Sign in</button>
    </form>

    <p class="auth-switch">Don’t have an account? <a href="<?= e(app_url('register.php')) ?>">Create one here</a>.</p>
</article>
