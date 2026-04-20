<?php declare(strict_types=1); ?>
<article class="auth-card">
    <p class="eyebrow">Student onboarding</p>
    <h2>Create account</h2>
    <p class="auth-card__meta">Register with your INTI student email. Accounts are activated immediately in V2.</p>

    <form method="POST" class="auth-form">
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="display_name">Full name</label>
            <input id="display_name" name="display_name" type="text" placeholder="Enter your name" value="<?= e((string) old('display_name')) ?>" required>
        </div>
        <div class="form-field">
            <label for="email">INTI email</label>
            <input id="email" name="email" type="email" placeholder="p23012345@student.newinti.edu.my" value="<?= e((string) old('email')) ?>" required>
            <p class="form-help">Only addresses ending in `@student.newinti.edu.my` are accepted.</p>
        </div>
        <div class="form-grid form-grid--two">
            <div class="form-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Minimum 8 characters" required>
            </div>
            <div class="form-field">
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter password" required>
            </div>
        </div>
        <button class="button button--primary" type="submit">Create account</button>
    </form>

    <p class="auth-switch">Already registered? <a href="<?= e(app_url('login.php')) ?>">Sign in here</a>.</p>
</article>
