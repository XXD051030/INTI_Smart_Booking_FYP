<?php declare(strict_types=1);

$messages = [];
foreach (['message', 'auth', 'admin_auth'] as $key) {
    $flash = pull_flash($key);
    if ($flash !== null) {
        $messages[] = $flash;
    }
}
?>
<?php if ($messages !== []): ?>
    <div class="alert-stack">
        <?php foreach ($messages as $flash): ?>
            <div class="flash flash--<?= e($flash['type'] ?? 'success') ?>" data-flash>
                <span><?= e($flash['message'] ?? '') ?></span>
                <button type="button" class="flash__close" data-dismiss-flash aria-label="Dismiss alert">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
