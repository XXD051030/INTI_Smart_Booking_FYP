<?php
require_once 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->prepare("
    SELECT b.id, b.date, b.start_time, b.end_time, f.name AS facility_name
    FROM bookings b
    JOIN facilities f ON b.facility_id = f.facility_id
    WHERE b.status = 'confirmed'
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($rows as $row) {
    $events[] = [
        'id'    => $row['id'],
        'title' => $row['facility_name'],
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end'   => $row['date'] . 'T' . $row['end_time']
    ];
}

echo json_encode($events);
?>
