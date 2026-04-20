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
$bookings = app()->bookings()->groupedForUser((int) $user['id']);
$now = new DateTimeImmutable();

app()->view()->render('student/profile', [
    'pageTitle' => 'Profile',
    'pageHeading' => 'Profile',
    'activeNav' => 'settings',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
    'stats' => [
        'total' => count($bookings),
        'upcoming' => count(array_filter($bookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'confirmed' && new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']) > $now)),
    ],
], 'student');
