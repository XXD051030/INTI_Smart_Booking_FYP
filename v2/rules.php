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

app()->view()->render('student/rules', [
    'pageTitle' => 'Rules',
    'pageHeading' => 'Reservation System Rules & Regulations',
    'activeNav' => 'rules',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
], 'student');
