<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database connection
require_once '../db.php';

// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'delete_user':
            $userId = $_POST['user_id'] ?? '';
            
            if (empty($userId) || !is_numeric($userId)) {
                echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
                exit;
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Delete related OTP records first
            $deleteOtpStmt = $pdo->prepare("DELETE FROM user_otp WHERE user_id = ?");
            $deleteOtpStmt->execute([$userId]);
            
            // Delete user
            $deleteUserStmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteUserStmt->execute([$userId]);
            
            if ($deleteUserStmt->rowCount() > 0) {
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;
            
        case 'edit_user':
            $userId = $_POST['user_id'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if (empty($userId) || !is_numeric($userId) || empty($username) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }
            
            // Check if username or email already exists (excluding current user)
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
            $checkStmt->execute([$username, $email, $userId]);
            
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                exit;
            }
            
            // Update user
            $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            $updateStmt->execute([$username, $email, $userId]);
            
            if ($updateStmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or user not found']);
            }
            break;
            
        case 'verify_user':
            $userId = $_POST['user_id'] ?? '';
            
            if (empty($userId) || !is_numeric($userId)) {
                echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
                exit;
            }
            
            // Update user verification status
            $verifyStmt = $pdo->prepare("UPDATE users SET is_verified = 1, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            $verifyStmt->execute([$userId]);
            
            if ($verifyStmt->rowCount() > 0) {
                // Also delete any pending OTP for this user
                $deleteOtpStmt = $pdo->prepare("DELETE FROM user_otp WHERE user_id = ?");
                $deleteOtpStmt->execute([$userId]);
                
                echo json_encode(['success' => true, 'message' => 'User verified successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found or already verified']);
            }
            break;
            
        case 'delete_otp':
            $otpId = $_POST['otp_id'] ?? '';
            
            if (empty($otpId) || !is_numeric($otpId)) {
                echo json_encode(['success' => false, 'message' => 'Invalid OTP ID']);
                exit;
            }
            
            // Delete OTP record
            $deleteOtpStmt = $pdo->prepare("DELETE FROM user_otp WHERE id = ?");
            $deleteOtpStmt->execute([$otpId]);
            
            if ($deleteOtpStmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'OTP record deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'OTP record not found']);
            }
            break;
            
        case 'bulk_delete_expired_otps':
            // Delete all expired OTP records
            $deleteExpiredStmt = $pdo->prepare("DELETE FROM user_otp WHERE expires_at < NOW()");
            $deleteExpiredStmt->execute();
            
            $deletedCount = $deleteExpiredStmt->rowCount();
            echo json_encode(['success' => true, 'message' => "Deleted {$deletedCount} expired OTP records"]);
            break;
            
        case 'reset_user_password':
            $userId = $_POST['user_id'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            if (empty($userId) || !is_numeric($userId) || empty($newPassword)) {
                echo json_encode(['success' => false, 'message' => 'User ID and new password are required']);
                exit;
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update user password
            $updateStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            $updateStmt->execute([$hashedPassword, $userId]);
            
            if ($updateStmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    error_log("Admin action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?> 