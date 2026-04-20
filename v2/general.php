<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_student();

$student = current_student();
$user = app()->users()->findById((int) $student['id']);
if ($user === null) {
    \V2\Support\Auth::logoutStudent();
    flash('auth', 'Your session is no longer valid.', 'error');
    redirect('login.php');
}

\V2\Support\Auth::loginStudent($user);
$notificationCount = app()->notificationService()->unreadCount((int) $user['id']);
$bookings = app()->bookings()->groupedForUser((int) $user['id']);
$now = new DateTimeImmutable();
$stats = [
    'total' => count($bookings),
    'confirmed' => count(array_filter($bookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'confirmed')),
    'cancelled' => count(array_filter($bookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'cancelled')),
    'completed' => count(array_filter($bookings, static fn (array $booking): bool => booking_display_status($booking) === 'Completed')),
    'upcoming' => count(array_filter($bookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'confirmed' && new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']) > $now)),
];

app()->view()->render('student/general', [
    'pageTitle' => 'General',
    'pageHeading' => 'Reservation Dashboard',
    'activeNav' => 'general',
    'currentUser' => $user,
    'notificationCount' => $notificationCount,
    'facilities' => app()->facilities()->allActive(),
    'stats' => $stats,
], 'student');
