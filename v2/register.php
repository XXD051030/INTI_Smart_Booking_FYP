<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_student() !== null) {
    redirect('general.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    remember_old_input($_POST);

    $result = app()->studentAuth()->register(
        (string) ($_POST['display_name'] ?? ''),
        (string) ($_POST['email'] ?? ''),
        (string) ($_POST['password'] ?? ''),
        (string) ($_POST['confirm_password'] ?? '')
    );

    if ($result['success']) {
        clear_old_input();
        flash('message', $result['message']);
        redirect('login.php');
    }

    flash('auth', $result['message'], 'error');
    redirect('register.php');
}

app()->view()->render('auth/register', [
    'pageTitle' => 'Register',
    'authTitle' => 'Book campus spaces with a cleaner V2 flow.',
    'authSubtitle' => 'The new portal keeps the same functional scope but removes OTP friction for the first release.',
    'authHighlights' => [
        'Register once with your INTI student email.',
        'Move directly into dashboard, booking, and calendar views.',
        'Stay aligned with the red, white, and black design direction.',
    ],
], 'auth');
