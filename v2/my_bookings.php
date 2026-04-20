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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    $result = app()->bookingService()->cancelStudentRequest(
        (string) ($_POST['request_token'] ?? ''),
        (int) $user['id'],
        $user['email']
    );
    flash('message', $result['message'], $result['success'] ? 'success' : 'error');
    redirect('my_bookings.php');
}

$activeFilter = (string) ($_GET['scope'] ?? 'all');
$allowedFilters = ['all', 'upcoming', 'past', 'cancelled'];
if (!in_array($activeFilter, $allowedFilters, true)) {
    $activeFilter = 'all';
}

$bookings = app()->bookings()->groupedForUser((int) $user['id']);
$now = new DateTimeImmutable();
$cancelBuffer = (int) config('booking.cancel_buffer_minutes', 30);

$bookings = array_map(static function (array $booking) use ($now, $cancelBuffer): array {
    $startAt = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']);
    $booking['can_cancel'] = ($booking['status'] ?? '') === 'confirmed' && ($startAt->getTimestamp() - $now->getTimestamp()) > ($cancelBuffer * 60);
    return $booking;
}, $bookings);

$filteredBookings = array_values(array_filter($bookings, static function (array $booking) use ($activeFilter, $now): bool {
    $displayStatus = booking_display_status($booking);
    $startAt = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']);

    return match ($activeFilter) {
        'upcoming' => ($booking['status'] ?? '') === 'confirmed' && $startAt > $now,
        'past' => $displayStatus === 'Completed',
        'cancelled' => $displayStatus === 'Cancelled',
        default => true,
    };
}));

$stats = [
    'total' => count($bookings),
    'completed' => count(array_filter($bookings, static fn (array $booking): bool => booking_display_status($booking) === 'Completed')),
    'cancelled' => count(array_filter($bookings, static fn (array $booking): bool => booking_display_status($booking) === 'Cancelled')),
    'upcoming' => count(array_filter($bookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'confirmed' && new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']) > $now)),
];

app()->view()->render('student/my_bookings', [
    'pageTitle' => 'My Bookings',
    'pageHeading' => 'My Bookings',
    'activeNav' => 'my_bookings',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
    'bookings' => $filteredBookings,
    'filters' => [
        'all' => 'All',
        'upcoming' => 'Upcoming',
        'past' => 'Past',
        'cancelled' => 'Cancelled',
    ],
    'activeFilter' => $activeFilter,
    'stats' => $stats,
], 'student');
