<?php
require_once 'db.php';

/**
 * Create a new notification for a user
 * 
 * @param int $user_id User ID
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param int|null $related_booking_id Related booking ID (optional)
 * @return bool Success status
 */
function createNotification($user_id, $type, $title, $message, $related_booking_id = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_booking_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$user_id, $type, $title, $message, $related_booking_id]);
    } catch (PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notifications for a user
 * 
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return
 * @param bool $unread_only Whether to return only unread notifications
 * @return array Array of notifications
 */
function getUserNotifications($user_id, $limit = 10, $unread_only = false) {
    global $pdo;
    
    try {
        // Validate and sanitize limit
        $limit = max(1, min(50, (int)$limit));
        
        $where_clause = "WHERE user_id = ?";
        $params = [$user_id];
        
        if ($unread_only) {
            $where_clause .= " AND is_read = FALSE";
        }
        
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, related_booking_id, is_read, created_at, read_at
            FROM notifications 
            $where_clause
            ORDER BY created_at DESC 
            LIMIT $limit
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Get unread notification count for a user
 * 
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationCount($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = FALSE
        ");
        
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    } catch (PDOException $e) {
        error_log("Error getting unread notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mark notification as read
 * 
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security)
 * @return bool Success status
 */
function markNotificationAsRead($notification_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        
        return $stmt->execute([$notification_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $user_id User ID
 * @return bool Success status
 */
function markAllNotificationsAsRead($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE user_id = ? AND is_read = FALSE
        ");
        
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete old notifications (older than 30 days)
 * 
 * @param int $user_id User ID (optional, if not provided, deletes for all users)
 * @return bool Success status
 */
function deleteOldNotifications($user_id = null) {
    global $pdo;
    
    try {
        if ($user_id) {
            $stmt = $pdo->prepare("
                DELETE FROM notifications 
                WHERE user_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->prepare("
                DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Error deleting old notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Create booking confirmation notification
 * 
 * @param int $user_id User ID
 * @param int $booking_id Booking ID
 * @param string $facility_name Facility name
 * @param string $booking_date Booking date
 * @param string $start_time Start time
 * @param string $end_time End time
 * @return bool Success status
 */
function createBookingConfirmationNotification($user_id, $booking_id, $facility_name, $booking_date, $start_time, $end_time) {
    $formatted_date = date('l, F j, Y', strtotime($booking_date));
    $formatted_start = date('g:i A', strtotime($start_time));
    $formatted_end = date('g:i A', strtotime($end_time));
    
    $title = "Booking Confirmed - $facility_name";
    $message = "Your booking for $facility_name on $formatted_date from $formatted_start to $formatted_end has been confirmed.";
    
    return createNotification($user_id, 'booking_confirmed', $title, $message, $booking_id);
}

/**
 * Create booking cancellation notification
 * 
 * @param int $user_id User ID
 * @param int $booking_id Booking ID
 * @param string $facility_name Facility name
 * @param string $booking_date Booking date
 * @param string $start_time Start time
 * @param string $end_time End time
 * @return bool Success status
 */
function createBookingCancellationNotification($user_id, $booking_id, $facility_name, $booking_date, $start_time, $end_time) {
    $formatted_date = date('l, F j, Y', strtotime($booking_date));
    $formatted_start = date('g:i A', strtotime($start_time));
    $formatted_end = date('g:i A', strtotime($end_time));
    
    $title = "Booking Cancelled - $facility_name";
    $message = "Your booking for $facility_name on $formatted_date from $formatted_start to $formatted_end has been cancelled.";
    
    return createNotification($user_id, 'booking_cancelled', $title, $message, $booking_id);
}

/**
 * Create booking reminder notification
 * 
 * @param int $user_id User ID
 * @param int $booking_id Booking ID
 * @param string $facility_name Facility name
 * @param string $booking_date Booking date
 * @param string $start_time Start time
 * @return bool Success status
 */
function createBookingReminderNotification($user_id, $booking_id, $facility_name, $booking_date, $start_time) {
    $formatted_date = date('l, F j, Y', strtotime($booking_date));
    $formatted_start = date('g:i A', strtotime($start_time));
    
    $title = "Booking Reminder - $facility_name";
    $message = "Reminder: Your booking for $facility_name is scheduled for today at $formatted_start.";
    
    return createNotification($user_id, 'booking_reminder', $title, $message, $booking_id);
}

/**
 * Create system notice notification
 * 
 * @param int $user_id User ID
 * @param string $title Notice title
 * @param string $message Notice message
 * @return bool Success status
 */
function createSystemNoticeNotification($user_id, $title, $message) {
    return createNotification($user_id, 'system_notice', $title, $message);
}

/**
 * Get notification icon and color based on type
 * 
 * @param string $type Notification type
 * @return array Array with icon and color
 */
function getNotificationIcon($type) {
    switch ($type) {
        case 'booking_confirmed':
            return ['icon' => 'fas fa-check-circle', 'color' => 'success'];
        case 'booking_cancelled':
            return ['icon' => 'fas fa-times-circle', 'color' => 'danger'];
        case 'booking_reminder':
            return ['icon' => 'fas fa-clock', 'color' => 'warning'];
        case 'system_notice':
            return ['icon' => 'fas fa-info-circle', 'color' => 'info'];
        default:
            return ['icon' => 'fas fa-bell', 'color' => 'primary'];
    }
}

/**
 * Format notification time for display
 * 
 * @param string $datetime DateTime string
 * @return string Formatted time
 */
function formatNotificationTime($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

/**
 * Create welcome notification for new users
 * 
 * @param int $user_id User ID
 * @return bool Success status
 */
function createWelcomeNotification($user_id) {
    $title = "Welcome to INTI Reservation System";
    $message = "Welcome to the new notification system! You will receive updates about your bookings and important announcements here.";
    
    return createNotification($user_id, 'system_notice', $title, $message);
}

?> 