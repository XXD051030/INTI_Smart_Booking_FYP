<?php
session_start();
require_once 'db.php';
require_once 'notification_functions.php';

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

// Get request parameters
$notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
$mark_all = isset($_POST['mark_all']) ? $_POST['mark_all'] === 'true' : false;

try {
    if ($mark_all) {
        // Mark all notifications as read
        $result = markAllNotificationsAsRead($user_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ]);
        }
    } else {
        // Mark specific notification as read
        if ($notification_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid notification ID'
            ]);
            exit;
        }
        
        $result = markNotificationAsRead($notification_id, $user_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("Error in mark_notification_read.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the request'
    ]);
}
?> 