<?php declare(strict_types=1); ?>
<section class="hero-block">
    <p class="eyebrow">Preferences</p>
    <h2>Settings</h2>
    <p>Keep the design structure from the prototype while grouping profile, language, support, and rules into one calm control surface.</p>
</section>

<section class="settings-grid">
    <article class="settings-card">
        <h3>Profile</h3>
        <p>View your name, derived student ID, email, and booking summary.</p>
        <a class="button button--outline" href="<?= e(app_url('profile.php')) ?>">View profile</a>
    </article>
    <article class="settings-card">
        <h3>Language</h3>
        <p>English is active for V2, with structure ready for future translations.</p>
        <a class="button button--outline" href="<?= e(app_url('language.php')) ?>">Change language</a>
    </article>
    <article class="settings-card">
        <h3>Support</h3>
        <p>Keep the contact card and support details in one obvious place for students.</p>
        <a class="button button--outline" href="<?= e(app_url('support.php')) ?>">Get support</a>
    </article>
    <article class="settings-card">
        <h3>Rules</h3>
        <p>Review the active booking policies, booking limits, and cancellation windows.</p>
        <a class="button button--outline" href="<?= e(app_url('rules.php')) ?>">View rules</a>
    </article>
</section>
