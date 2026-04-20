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

$activeType = (string) ($_GET['type'] ?? 'all');
$allowedTypes = ['all', 'discussion_room', 'basketball_court', 'stem_lab'];
if (!in_array($activeType, $allowedTypes, true)) {
    $activeType = 'all';
}

$allFacilities = app()->facilities()->allActive();
$facilities = array_values(array_filter($allFacilities, static fn (array $facility): bool => $activeType === 'all' || $facility['type'] === $activeType));
$selectedFacility = null;
$selectedFacilityId = (int) ($_GET['facility'] ?? 0);
if ($selectedFacilityId > 0) {
    foreach ($allFacilities as $facility) {
        if ((int) $facility['id'] === $selectedFacilityId) {
            $selectedFacility = $facility;
            break;
        }
    }
}

$selectedDate = (string) ($_GET['date'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    remember_old_input($_POST);

    $selectedFacilityId = (int) ($_POST['facility_id'] ?? 0);
    $selectedDate = (string) ($_POST['booking_date'] ?? '');
    $result = app()->bookingService()->createStudentBooking(
        (int) $user['id'],
        $selectedFacilityId,
        $selectedDate,
        (array) ($_POST['slots'] ?? []),
        (string) ($_POST['purpose'] ?? ''),
        $user['email']
    );

    if ($result['success']) {
        clear_old_input();
        flash('message', $result['message']);
        redirect('my_bookings.php');
    }

    flash('message', $result['message'], 'error');
    $query = http_build_query(array_filter([
        'facility' => $selectedFacilityId ?: null,
        'date' => $selectedDate ?: null,
        'type' => $activeType !== 'all' ? $activeType : null,
    ]));
    redirect('booking.php' . ($query === '' ? '' : '?' . $query));
}

$availability = [];
$dateBounds = ['min' => date('Y-m-d'), 'max' => date('Y-m-d')];
$dailyRequestCount = 0;
$dailyLimit = (int) config('booking.max_request_count_per_day', 2);

if ($selectedFacility !== null) {
    $today = new DateTimeImmutable('today');
    $dateBounds['min'] = $today->format('Y-m-d');
    $dateBounds['max'] = $today->modify('+' . (int) $selectedFacility['advance_booking_days'] . ' day')->format('Y-m-d');

    if ($selectedDate !== '') {
        $availability = app()->bookingService()->availability((int) $selectedFacility['id'], $selectedDate);
        $dailyRequestCount = app()->bookings()->countRequestTokensForUserOnDate((int) $user['id'], $selectedDate);
    }
}

app()->view()->render('student/booking', [
    'pageTitle' => 'Booking',
    'pageHeading' => 'Book Facilities',
    'activeNav' => 'booking',
    'currentUser' => $user,
    'notificationCount' => app()->notificationService()->unreadCount((int) $user['id']),
    'facilities' => $facilities,
    'selectedFacility' => $selectedFacility,
    'selectedDate' => $selectedDate,
    'availability' => $availability,
    'dateBounds' => $dateBounds,
    'dailyRequestCount' => $dailyRequestCount,
    'dailyLimit' => $dailyLimit,
    'maxSlots' => (int) config('booking.max_consecutive_slots', 2),
    'activeType' => $activeType,
    'facilityTypes' => [
        'all' => 'All',
        'discussion_room' => 'Discussion',
        'basketball_court' => 'Sport',
        'stem_lab' => 'STEM',
    ],
    'pageScripts' => ['booking.js'],
], 'student');
