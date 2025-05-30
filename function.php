<?php
/**
 * Common utility functions for the application
 */

/**
 * Redirect to a specified page
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Sanitize input data
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 * @param PDO $pdo Database connection
 * @return array|false User data or false if not found
 */
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?> 