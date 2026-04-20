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

$monthQuery = (string) ($_GET['month'] ?? date('Y-m'));
$month = DateTimeImmutable::createFromFormat('Y-m-d', $monthQuery . '-01') ?: new DateTimeImmutable('first day of this month');
$monthStart = $month->modify('first day of this month');
$monthEnd = $month->modify('last day of this month');
$calendarStart = $monthStart->modify('monday this week');
$calendarEnd = $monthEnd->modify('sunday this week');

$allBookings = app()->bookings()->groupedForUser((int) $user['id']);
$monthBookings = app()->bookings()->groupedForCalendar((int) $user['id'], $calendarStart->format('Y-m-d'), $calendarEnd->format('Y-m-d'));
$eventsByDate = [];
foreach ($monthBookings as $booking) {
    $eventsByDate[$booking['booking_date']][] = $booking;
}

$calendarDays = [];
$cursor = $calendarStart;
while ($cursor <= $calendarEnd) {
    $dateKey = $cursor->format('Y-m-d');
    $calendarDays[] = [
        'day' => $cursor->format('j'),
        'date' => $dateKey,
        'currentMonth' => $cursor->format('m') === $monthStart->format('m'),
        'isToday' => $dateKey === date('Y-m-d'),
        'events' => $eventsByDate[$dateKey] ?? [],
    ];
    $cursor = $cursor->modify('+1 day');
}

$now = new DateTimeImmutable();
$weekStart = $now->modify('monday this week');
$weekEnd = $now->modify('sunday this week');

$stats = [
    'total' => count($allBookings),
    'month' => count(array_filter($allBookings, static fn (array $booking): bool => $booking['booking_date'] >= $monthStart->format('Y-m-d') && $booking['booking_date'] <= $monthEnd->format('Y-m-d'))),
    'upcoming' => count(array_filter($allBookings, static fn (array $booking): bool => ($booking['status'] ?? '') === 'confirmed' && new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']) > $now)),
    'week' => count(array_filter($allBookings, static fn (array $booking): bool => $booking['booking_date'] >= $weekStart->format('Y-m-d') && $booking['booking_date'] <= $weekEnd->format('Y-m-d'))),
];

app()->view()->render('student/calendar', [
    'pageTitle' => 'Calendar',
    'pageHeading' => 'Calendar View',
    'activeNav' => 'calendar',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
    'monthLabel' => $monthStart->format('F Y'),
    'previousMonth' => $monthStart->modify('-1 month')->format('Y-m'),
    'nextMonth' => $monthStart->modify('+1 month')->format('Y-m'),
    'calendarDays' => $calendarDays,
    'stats' => $stats,
], 'student');
