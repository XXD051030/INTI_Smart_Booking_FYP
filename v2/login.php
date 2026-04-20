<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_student() !== null) {
    redirect('general.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    remember_old_input($_POST);

    $result = app()->studentAuth()->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
    if ($result['success']) {
        clear_old_input();
        flash('message', $result['message']);
        redirect('general.php');
    }

    flash('auth', $result['message'], 'error');
    redirect('login.php');
}

app()->view()->render('auth/login', [
    'pageTitle' => 'Sign in',
    'authTitle' => 'Welcome back',
    'authSubtitle' => 'Sign in to your booking account and keep facility reservations moving without friction.',
    'authHighlights' => [
        'Centralised booking management for facilities, labs, and sports spaces.',
        'Real-time status across bookings, calendar, and notifications.',
        'A cleaner flow designed around your original FYP prototype.',
    ],
], 'auth');
