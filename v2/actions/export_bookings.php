<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

require_admin();

$filters = [
    'date' => (string) ($_GET['date'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''),
    'facility_id' => (string) ($_GET['facility_id'] ?? ''),
    'search' => trim((string) ($_GET['search'] ?? '')),
];

$bookings = app()->bookings()->groupedForAdmin($filters);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="v2_bookings_export.csv"');

$output = fopen('php://output', 'wb');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($output, ['Booking ID', 'Student', 'Email', 'Facility', 'Date', 'Time', 'Status', 'Purpose']);

foreach ($bookings as $booking) {
    fputcsv($output, [
        format_booking_code((int) $booking['booking_id']),
        $booking['display_name'],
        $booking['email'],
        $booking['facility_name'],
        $booking['booking_date'],
        format_time_range($booking['start_time'], $booking['end_time']),
        booking_display_status($booking),
        $booking['purpose'],
    ]);
}

fclose($output);
exit;
