<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

if (current_admin() !== null) {
    redirect('admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    remember_old_input($_POST);

    $result = app()->adminAuth()->login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
    if ($result['success']) {
        clear_old_input();
        flash('message', $result['message']);
        redirect('admin/dashboard.php');
    }

    flash('admin_auth', $result['message'], 'error');
    redirect('admin/index.php');
}

app()->view()->render('admin/login', [
    'pageTitle' => 'Admin sign in',
    'authTitle' => 'Admin operations, rebuilt for V2.',
    'authSubtitle' => 'Use the separate admin account table to manage users, exports, booking status, and password resets.',
    'authHighlights' => [
        'Independent admin authentication, no hardcoded runtime check.',
        'Booking status management aligned with the prototype.',
        'CSV exports and student lifecycle tools in one console.',
    ],
], 'auth');
