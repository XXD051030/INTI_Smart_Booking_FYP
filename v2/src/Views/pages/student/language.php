<?php declare(strict_types=1); ?>
<section class="panel">
    <p class="eyebrow">Language</p>
    <h2>Select language</h2>
    <p>V2 currently ships in English only, but the preference is persisted so more languages can be added later without changing the page flow.</p>

    <form method="POST" class="stack-form">
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="preferred_language">Choose a language</label>
            <select id="preferred_language" name="preferred_language" class="settings-select">
                <option value="en" <?= ($currentUser['preferred_language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>
        <div class="inline-actions">
            <button type="submit" class="button button--primary">Save language</button>
            <a href="<?= e(app_url('settings.php')) ?>" class="button button--ghost">Back to settings</a>
        </div>
    </form>
</section>
