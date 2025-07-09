<?php
require_once 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->prepare("
    SELECT b.booking_id, b.booking_date, b.start_time, b.end_time, f.name AS facility_name
    FROM bookings b
    JOIN facilities f ON b.facility_id = f.facility_id
    WHERE b.status = 'confirmed'
");
$stmt->execute();

$events = [];
foreach ($stmt->fetchAll() as $row) {
    $events[] = [
        'id'    => $row['booking_id'],
        'title' => $row['facility_name'],
        'start' => $row['booking_date'] . 'T' . $row['start_time'],
        'end'   => $row['booking_date'] . 'T' . $row['end_time']
    ];
}

echo json_encode($events);
?>
