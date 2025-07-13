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

$user_id = $_SESSION['user_id'];

// Get request parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$unread_only = isset($_GET['unread_only']) ? $_GET['unread_only'] === 'true' : false;

// Validate limit
if ($limit < 1 || $limit > 50) {
    $limit = 10;
}

try {
    // Get notifications
    $notifications = getUserNotifications($user_id, $limit, $unread_only);
    
    // Get unread count
    $unread_count = getUnreadNotificationCount($user_id);
    
    // Format notifications for display
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        $icon_info = getNotificationIcon($notification['type']);
        $formatted_notifications[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'related_booking_id' => $notification['related_booking_id'],
            'is_read' => (bool)$notification['is_read'],
            'created_at' => $notification['created_at'],
            'read_at' => $notification['read_at'],
            'time_formatted' => formatNotificationTime($notification['created_at']),
            'icon' => $icon_info['icon'],
            'color' => $icon_info['color']
        ];
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications,
        'unread_count' => $unread_count,
        'total_count' => count($formatted_notifications)
    ]);

} catch (Exception $e) {
    error_log("Error in get_notifications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch notifications'
    ]);
}
?> 