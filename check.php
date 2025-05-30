<?php
session_start();
require_once 'db.php';

// Google OAuth configuration - using environment variables for security
$client_id = $_ENV['823488326203-mf0v62tjt1eabdp1eb20p1knngbeefsv.apps.googleusercontent.com'] ?? '';
$client_secret = $_ENV['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'] ?? '';
$redirect_uri = 'https://gesturo.lol/check.php';

if (isset($_GET['code'])) {
    // Exchange authorization code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_POST, true);
    $token_response = curl_exec($ch);
    curl_close($ch);

    $token_info = json_decode($token_response, true);

    if (isset($token_info['access_token'])) {
        // Get user info from Google
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($userinfo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_info['access_token']
        ]);
        $userinfo_response = curl_exec($ch);
        curl_close($ch);

        $user_info = json_decode($userinfo_response, true);

        if (isset($user_info['email'])) {
            // Check if user exists in database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$user_info['email']]);
            $user = $stmt->fetch();

            if ($user) {
                // User exists - log them in
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                header('Location: https://gesturo.lol/homepage.php');
                exit();
            } else {
                // Create new user account using Google display name
                $username = $user_info['name']; // Use Google display name
                // Remove any special characters and spaces from username
                $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
                // If username is empty after cleaning, use email username as fallback
                if (empty($username)) {
                    $username = explode('@', $user_info['email'])[0];
                }
                
                $password = bin2hex(random_bytes(16)); // Generate random password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_verified) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$username, $user_info['email'], $hashed_password]);
                    
                    // Get the new user's ID
                    $user_id = $pdo->lastInsertId();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $user_info['email'];
                    
                    header('Location: https://gesturo.lol/');
                    exit();
                } catch (PDOException $e) {
                    // Handle duplicate username error
                    if ($e->getCode() == 23000) {
                        $username = $username . rand(100, 999);
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $user_info['email'], $hashed_password]);
                        
                        $user_id = $pdo->lastInsertId();
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $user_info['email'];
                        
                        header('Location: homepage.php');
                        exit();
                    }
                    die("Error creating account: " . $e->getMessage());
                }
            }
        }
    }
}

// If we get here, something went wrong
header('Location: login.php?error=google_auth_failed');
exit();
?>