<?php
session_start();
require_once 'db.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check what action is requested
    $action = $_POST['action'] ?? 'check_availability';
    
    if ($action === 'check_daily_count') {
        // Check daily booking count for user
        $date = $_POST['date'] ?? '';
        
        if (empty($date)) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE user_id = ? 
            AND booking_date = ? 
            AND status = 'confirmed'
        ");
        $stmt->execute([$user_id, $date]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'count' => (int)$result['count']
        ]);
        
    } else {
        // Default action: check availability for facility and date
        $facility_id = $_POST['facility_id'] ?? '';
        $date = $_POST['date'] ?? '';
        
        if (empty($facility_id) || empty($date)) {
            echo json_encode(['success' => false, 'message' => 'Facility ID and date are required']);
            exit;
        }
        
        // Validate facility exists and is active
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE facility_id = ? AND is_active = 1");
        $stmt->execute([$facility_id]);
        $facility = $stmt->fetch();
        
        if (!$facility) {
            echo json_encode(['success' => false, 'message' => 'Facility not found or inactive']);
            exit;
        }
        
        // Validate date is not in the past
        $today = date('Y-m-d');
        if ($date < $today) {
            echo json_encode(['success' => false, 'message' => 'Cannot book for past dates']);
            exit;
        }
        
        // Check if date is within advance booking limit
        $max_date = date('Y-m-d', strtotime($today . ' + ' . $facility['advance_booking_days'] . ' days'));
        if ($date > $max_date) {
            echo json_encode(['success' => false, 'message' => 'Date exceeds advance booking limit']);
            exit;
        }
        
        // Get all existing bookings for this facility on this date
        $stmt = $pdo->prepare("
            SELECT start_time, end_time 
            FROM bookings 
            WHERE facility_id = ? 
            AND booking_date = ? 
            AND status = 'confirmed'
        ");
        $stmt->execute([$facility_id, $date]);
        $existing_bookings = $stmt->fetchAll();
        
        // Create array of booked time slots
        $booked_slots = [];
        foreach ($existing_bookings as $booking) {
            $booked_slots[] = substr($booking['start_time'], 0, 5); // Format HH:MM
        }
        
        // Operating hours: 08:00 - 17:00 (9 time slots)
        $time_slots = [
            '08:00', '09:00', '10:00', '11:00', 
            '12:00', '13:00', '14:00', '15:00', '16:00'
        ];
        
        // Check availability for each time slot
        $available_slots = [];
        foreach ($time_slots as $time) {
            $available_slots[] = [
                'time' => $time,
                'available' => !in_array($time, $booked_slots)
            ];
        }
        
        echo json_encode([
            'success' => true,
            'available_slots' => $available_slots,
            'max_date' => $max_date
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in check_availability.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in check_availability.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?> 