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
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'mark_all_read') {
        app()->notificationService()->markAllRead((int) $user['id']);
    }

    if ($action === 'mark_read' && isset($_POST['notification_id'])) {
        app()->notificationService()->markRead((int) $_POST['notification_id'], (int) $user['id']);
    }

    flash('message', 'Notifications updated.');
    redirect('notifications.php');
}

\V2\Support\Auth::loginStudent($user);

app()->view()->render('student/notifications', [
    'pageTitle' => 'Notifications',
    'pageHeading' => 'Notifications',
    'activeNav' => 'settings',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
    'notifications' => app()->notificationService()->listForUser((int) $user['id']),
], 'student');
