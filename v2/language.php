<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_student();

$user = app()->users()->findById((int) current_student()['id']);
if ($user === null) {
    \V2\Support\Auth::logoutStudent();
    flash('auth', 'Your session is no longer valid.', 'error');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    $language = (string) ($_POST['preferred_language'] ?? 'en');
    app()->users()->updateLanguage((int) $user['id'], $language === 'en' ? 'en' : 'en');
    $user = app()->users()->findById((int) $user['id']);
    \V2\Support\Auth::loginStudent($user);
    flash('message', 'Language preference updated.');
    redirect('language.php');
}

\V2\Support\Auth::loginStudent($user);

app()->view()->render('student/language', [
    'pageTitle' => 'Language',
    'pageHeading' => 'Language',
    'activeNav' => 'settings',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
], 'student');
