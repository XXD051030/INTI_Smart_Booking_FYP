<?php declare(strict_types=1); ?>
<section class="profile-panel">
    <div class="profile-identity">
        <div class="profile-avatar"><?= e(substr($currentUser['display_name'], 0, 1)) ?></div>
        <div>
            <p class="eyebrow">Student profile</p>
            <h2><?= e($currentUser['display_name']) ?></h2>
            <p><?= e($currentUser['email']) ?></p>
        </div>
    </div>
</section>

<section class="detail-grid">
    <article class="detail-card">
        <strong>Student ID</strong>
        <p><?= e(student_id_from_email($currentUser['email'])) ?></p>
    </article>
    <article class="detail-card">
        <strong>Preferred language</strong>
        <p><?= e(strtoupper($currentUser['preferred_language'])) ?></p>
    </article>
    <article class="detail-card">
        <strong>Total requests</strong>
        <p><?= e((string) $stats['total']) ?></p>
    </article>
    <article class="detail-card">
        <strong>Upcoming requests</strong>
        <p><?= e((string) $stats['upcoming']) ?></p>
    </article>
</section>
