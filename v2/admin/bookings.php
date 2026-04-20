<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

require_admin();

$currentAdmin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    if ((string) ($_POST['action'] ?? '') === 'cancel_booking') {
        $result = app()->bookingService()->cancelAdminRequest((string) ($_POST['request_token'] ?? ''));
        flash('message', $result['message'], $result['success'] ? 'success' : 'error');
    }

    redirect('admin/bookings.php' . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
}

$filters = [
    'date' => (string) ($_GET['date'] ?? date('Y-m-d')),
    'status' => (string) ($_GET['status'] ?? ''),
    'facility_id' => (string) ($_GET['facility_id'] ?? ''),
    'search' => trim((string) ($_GET['search'] ?? '')),
];

$bookings = app()->bookings()->groupedForAdmin($filters);
$selectedBooking = isset($_GET['request']) ? app()->bookings()->findGroupedByToken((string) $_GET['request']) : null;

app()->view()->render('admin/bookings', [
    'pageTitle' => 'Booking Status',
    'pageHeading' => 'Booking Status',
    'activeNav' => 'bookings',
    'currentAdmin' => $currentAdmin,
    'bookings' => $bookings,
    'selectedBooking' => $selectedBooking,
    'facilities' => app()->facilities()->allActive(),
    'filters' => $filters,
], 'admin');
