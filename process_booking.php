<?php
session_start();
require_once 'db.php';
require_once 'Mailer.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get POST data
    $facility_id = $_POST['facility_id'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $time_slots_json = $_POST['time_slots'] ?? '';
    $slot_count = (int)($_POST['slot_count'] ?? 1);

    // Parse time slots if provided
    $time_slots = [];
    if (!empty($time_slots_json)) {
        $time_slots = json_decode($time_slots_json, true);
        if (!is_array($time_slots)) {
            $time_slots = [$start_time]; // Fallback to single slot
        }
    } else {
        $time_slots = [$start_time]; // Single slot booking
    }

    // Validate required fields
    if (empty($facility_id) || empty($booking_date) || empty($start_time) || empty($end_time) || empty($purpose)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate slot count and array consistency
    if (count($time_slots) > 2) {
        echo json_encode(['success' => false, 'message' => 'Maximum 2 consecutive time slots allowed']);
        exit;
    }

    if (count($time_slots) > 1) {
        // Validate consecutive slots
        sort($time_slots);
        $valid_times = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
        for ($i = 1; $i < count($time_slots); $i++) {
            $prev_index = array_search($time_slots[$i-1], $valid_times);
            $curr_index = array_search($time_slots[$i], $valid_times);
            if ($curr_index - $prev_index !== 1) {
                echo json_encode(['success' => false, 'message' => 'Time slots must be consecutive']);
                exit;
            }
        }
    }

    // Validate purpose length
    if (strlen($purpose) < 10) {
        echo json_encode(['success' => false, 'message' => 'Purpose must be at least 10 characters long']);
        exit;
    }

    if (strlen($purpose) > 500) {
        echo json_encode(['success' => false, 'message' => 'Purpose cannot exceed 500 characters']);
        exit;
    }

    // Get user information
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
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
    if ($booking_date < $today) {
        echo json_encode(['success' => false, 'message' => 'Cannot book for past dates']);
        exit;
    }

    // Check if date is within advance booking limit
    $max_date = date('Y-m-d', strtotime($today . ' + ' . $facility['advance_booking_days'] . ' days'));
    if ($booking_date > $max_date) {
        echo json_encode(['success' => false, 'message' => 'Date exceeds advance booking limit']);
        exit;
    }

    // Check daily booking limit (max 2 bookings per day)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE user_id = ? 
        AND booking_date = ? 
        AND status = 'confirmed'
    ");
    $stmt->execute([$user_id, $booking_date]);
    $daily_count = $stmt->fetch()['count'];
    
    if ($daily_count >= 2) {
        echo json_encode(['success' => false, 'message' => 'You have reached your daily booking limit of 2 slots']);
        exit;
    }

    // Check if any of the time slots are already booked
    $slot_placeholders = str_repeat('?,', count($time_slots) - 1) . '?';
    $check_params = [$facility_id, $booking_date];
    $check_params = array_merge($check_params, $time_slots);
    
    $stmt = $pdo->prepare("
        SELECT start_time, COUNT(*) as count 
        FROM bookings 
        WHERE facility_id = ? 
        AND booking_date = ? 
        AND start_time IN ($slot_placeholders)
        AND status = 'confirmed'
        GROUP BY start_time
    ");
    $stmt->execute($check_params);
    $conflicting_slots = $stmt->fetchAll();
    
    if (!empty($conflicting_slots)) {
        $conflicting_times = array_column($conflicting_slots, 'start_time');
        echo json_encode(['success' => false, 'message' => 'One or more selected time slots are already booked: ' . implode(', ', $conflicting_times)]);
        exit;
    }

    // Validate all time slots are within operating hours (08:00 - 17:00)
    $valid_start_times = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
    foreach ($time_slots as $slot) {
        if (!in_array($slot, $valid_start_times)) {
            echo json_encode(['success' => false, 'message' => 'Invalid time slot: ' . $slot]);
            exit;
        }
    }

    // Start database transaction
    $pdo->beginTransaction();

    // Insert bookings for each time slot
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, facility_id, booking_date, start_time, end_time, purpose, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    
    $booking_ids = [];
    foreach ($time_slots as $slot_start) {
        // Calculate end time for each slot (1 hour later)
        $slot_end = date('H:i', strtotime($slot_start . ' +1 hour'));
        
        $result = $stmt->execute([
            $user_id, 
            $facility_id, 
            $booking_date, 
            $slot_start, 
            $slot_end, 
            $purpose
        ]);

        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create booking for slot: ' . $slot_start]);
            exit;
        }
        
        $booking_ids[] = $pdo->lastInsertId();
    }

    $primary_booking_id = $booking_ids[0]; // Use first booking ID as primary

    // Commit transaction
    $pdo->commit();

    // Send confirmation email
    try {
        $mailer = new Mailer();
        
        $subject = "Booking Confirmation - {$facility['name']}";
        
        $formatted_date = date('l, F j, Y', strtotime($booking_date));
        
        // Format time display for multiple slots
        if (count($time_slots) === 1) {
            $formatted_start_time = date('g:i A', strtotime($time_slots[0]));
            $formatted_end_time = date('g:i A', strtotime($time_slots[0] . ' +1 hour'));
            $time_display = $formatted_start_time . ' - ' . $formatted_end_time;
            $duration_text = '(1 hour)';
        } else {
            $formatted_start_time = date('g:i A', strtotime($time_slots[0]));
            $formatted_end_time = date('g:i A', strtotime($time_slots[count($time_slots)-1] . ' +1 hour'));
            $time_display = $formatted_start_time . ' - ' . $formatted_end_time;
            $duration_text = '(' . count($time_slots) . ' hours, consecutive slots)';
        }
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #4285F4; color: white; padding: 20px; text-align: center;'>
                <h1>Booking Confirmation</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <h2>Dear {$user['username']},</h2>
                <p>Your booking has been confirmed! Here are the details:</p>
                
                <div style='background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #4285F4; margin-top: 0;'>Booking Details</h3>
                    <p><strong>Booking ID:</strong> #{$primary_booking_id}" . (count($booking_ids) > 1 ? ' (and ' . (count($booking_ids)-1) . ' more)' : '') . "</p>
                    <p><strong>Facility:</strong> {$facility['name']}</p>
                    <p><strong>Location:</strong> {$facility['location']}</p>
                    <p><strong>Date:</strong> {$formatted_date}</p>
                    <p><strong>Time:</strong> {$time_display} {$duration_text}</p>
                    <p><strong>Purpose:</strong> {$purpose}</p>
                </div>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h4 style='color: #856404; margin-top: 0;'>Important Notes:</h4>
                    <ul style='color: #856404;'>
                        <li>Please arrive on time for your booking</li>
                        <li>You can cancel your booking up to 30 minutes before the start time</li>
                        <li>Bring your student ID for verification</li>
                        <li>Follow all facility rules and regulations</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://{$_SERVER['HTTP_HOST']}/my_bookings.php' style='background-color: #4285F4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                        View My Bookings
                    </a>
                </div>
                
                <p>If you need to cancel or modify your booking, please visit the booking system.</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #dee2e6;'>
                
                <p style='color: #6c757d; font-size: 14px;'>
                    This is an automated message from INTI Reservation System. Please do not reply to this email.
                    <br>If you have any questions, please contact the facility management.
                </p>
            </div>
        </div>
        ";
        
        $mail_result = $mailer->sendMail($user['email'], $subject, $body);
        
        if (!$mail_result) {
            error_log("Failed to send booking confirmation email to: " . $user['email']);
        }
        
    } catch (Exception $e) {
        error_log("Email error in process_booking.php: " . $e->getMessage());
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed successfully!',
        'booking_ids' => $booking_ids,
        'primary_booking_id' => $primary_booking_id,
        'slot_count' => count($time_slots),
        'facility_name' => $facility['name'],
        'booking_date' => $formatted_date,
        'booking_time' => $time_display . ' ' . $duration_text
    ]);

} catch (PDOException $e) {
    // Rollback transaction if it was started
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Database error in process_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("General error in process_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your booking']);
}
?> 