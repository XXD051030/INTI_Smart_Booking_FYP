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

\V2\Support\Auth::loginStudent($user);

app()->view()->render('student/support', [
    'pageTitle' => 'Support',
    'pageHeading' => 'Support',
    'activeNav' => 'settings',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
], 'student');
