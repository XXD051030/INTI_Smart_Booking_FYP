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
$booking_id = $_POST['booking_id'] ?? '';

if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit;
}

try {
    // Get booking details and verify ownership
    $stmt = $pdo->prepare("
        SELECT b.*, f.name as facility_name, f.location, u.username, u.email
        FROM bookings b 
        JOIN facilities f ON b.facility_id = f.facility_id 
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
        exit;
    }

    // Check if booking is already cancelled
    if ($booking['status'] === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Booking is already cancelled']);
        exit;
    }

    // Check if booking is in the past
    $booking_datetime = $booking['booking_date'] . ' ' . $booking['start_time'];
    $booking_timestamp = strtotime($booking_datetime);
    $current_timestamp = time();
    
    if ($booking_timestamp <= $current_timestamp) {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel past bookings']);
        exit;
    }

    // Check cancellation time limit (30 minutes before start time)
    $time_diff_minutes = ($booking_timestamp - $current_timestamp) / 60;
    if ($time_diff_minutes <= 30) {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel booking within 30 minutes of start time']);
        exit;
    }

    // Start database transaction
    $pdo->beginTransaction();

    // Update booking status to cancelled
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = 'cancelled', cancelled_at = NOW() 
        WHERE booking_id = ? AND user_id = ?
    ");
    
    $result = $stmt->execute([$booking_id, $user_id]);

    if (!$result) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
        exit;
    }

    // Commit transaction
    $pdo->commit();

    // Send cancellation email
    try {
        $mailer = new Mailer();
        
        $subject = "Booking Cancelled - {$booking['facility_name']}";
        
        $formatted_date = date('l, F j, Y', strtotime($booking['booking_date']));
        $formatted_start_time = date('g:i A', strtotime($booking['start_time']));
        $formatted_end_time = date('g:i A', strtotime($booking['end_time']));
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #dc3545; color: white; padding: 20px; text-align: center;'>
                <h1>Booking Cancelled</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <h2>Dear {$booking['username']},</h2>
                <p>Your booking has been successfully cancelled. Here are the details of the cancelled booking:</p>
                
                <div style='background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #dc3545; margin-top: 0;'>Cancelled Booking Details</h3>
                    <p><strong>Booking ID:</strong> #{$booking['booking_id']}</p>
                    <p><strong>Facility:</strong> {$booking['facility_name']}</p>
                    <p><strong>Location:</strong> {$booking['location']}</p>
                    <p><strong>Date:</strong> {$formatted_date}</p>
                    <p><strong>Time:</strong> {$formatted_start_time} - {$formatted_end_time}</p>
                    <p><strong>Purpose:</strong> {$booking['purpose']}</p>
                    <p><strong>Cancelled At:</strong> " . date('l, F j, Y g:i A') . "</p>
                </div>
                
                <div style='background-color: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                    <h4 style='color: #0c5460; margin-top: 0;'>What's Next?</h4>
                    <ul style='color: #0c5460;'>
                        <li>The time slot is now available for other users to book</li>
                        <li>You can make a new booking anytime through the reservation system</li>
                        <li>Remember to cancel bookings at least 30 minutes in advance</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://{$_SERVER['HTTP_HOST']}/booking.php' style='background-color: #4285F4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; margin-right: 10px;'>
                        Make New Booking
                    </a>
                    <a href='http://{$_SERVER['HTTP_HOST']}/my_bookings.php' style='background-color: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                        View My Bookings
                    </a>
                </div>
                
                <p>If you have any questions or need assistance, please contact the facility management.</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #dee2e6;'>
                
                <p style='color: #6c757d; font-size: 14px;'>
                    This is an automated message from INTI Reservation System. Please do not reply to this email.
                    <br>If you have any questions, please contact the facility management.
                </p>
            </div>
        </div>
        ";
        
        $mail_result = $mailer->sendMail($booking['email'], $subject, $body);
        
        if (!$mail_result) {
            error_log("Failed to send booking cancellation email to: " . $booking['email']);
        }
        
    } catch (Exception $e) {
        error_log("Email error in cancel_booking.php: " . $e->getMessage());
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully',
        'booking_id' => $booking_id,
        'facility_name' => $booking['facility_name'],
        'booking_date' => $formatted_date,
        'booking_time' => $formatted_start_time . ' - ' . $formatted_end_time
    ]);

} catch (PDOException $e) {
    // Rollback transaction if it was started
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Database error in cancel_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("General error in cancel_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling your booking']);
}
?> 