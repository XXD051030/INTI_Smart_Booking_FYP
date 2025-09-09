<?php
/**
 * INTI Smart Booking System - Create Default User Script
 * 
 * This script creates a default user for testing and initial access
 * 
 * Usage: php create_default_user.php
 * Web: Navigate to create_default_user.php in browser
 */

// Include database connection
require_once 'db.php';
require_once 'function.php';

// ANSI color codes for terminal output
class Colors {
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const BOLD = "\033[1m";
    const RESET = "\033[0m";
}

/**
 * Print colored output to terminal
 */
function printMessage($message, $color = Colors::RESET, $bold = false) {
    if (php_sapi_name() === 'cli') {
        $prefix = $bold ? Colors::BOLD : '';
        echo $prefix . $color . $message . Colors::RESET . "\n";
    } else {
        // Web output
        $style = '';
        if ($color === Colors::GREEN) $style = 'color: green;';
        if ($color === Colors::RED) $style = 'color: red;';
        if ($color === Colors::YELLOW) $style = 'color: orange;';
        if ($color === Colors::BLUE) $style = 'color: blue;';
        if ($bold) $style .= ' font-weight: bold;';
        
        echo "<p style='$style'>$message</p>";
    }
}

/**
 * Create default user
 */
function createDefaultUser($pdo) {
    // Default user configuration
    $defaultUsers = [
        [
            'username' => 'admin',
            'email' => 'admin@student.newinti.edu.my',
            'password' => 'admin123',
            'is_verified' => 1
        ],
        [
            'username' => 'testuser',
            'email' => 'test@student.newinti.edu.my', 
            'password' => 'test123',
            'is_verified' => 1
        ]
    ];
    
    printMessage("🎯 Creating Default Users", Colors::BLUE, true);
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
    
    foreach ($defaultUsers as $userData) {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$userData['email']]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                if ($existingUser['is_verified'] == 1) {
                    printMessage("ℹ️  User '{$userData['username']}' ({$userData['email']}) already exists and is verified", Colors::YELLOW);
                } else {
                    // Update existing unverified user
                    $password_hash = password_hash($userData['password'], PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, is_verified = ? WHERE email = ?");
                    
                    if ($updateStmt->execute([$userData['username'], $password_hash, $userData['is_verified'], $userData['email']])) {
                        printMessage("✅ Updated and verified existing user: {$userData['username']} ({$userData['email']})", Colors::GREEN);
                    } else {
                        printMessage("❌ Failed to update user: {$userData['username']}", Colors::RED);
                    }
                }
            } else {
                // Create new user
                $password_hash = password_hash($userData['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_verified, created_at) VALUES (?, ?, ?, ?, NOW())");
                
                if ($stmt->execute([$userData['username'], $userData['email'], $password_hash, $userData['is_verified']])) {
                    printMessage("✅ Created new user: {$userData['username']} ({$userData['email']})", Colors::GREEN);
                    
                    // Get the user ID for notification
                    $userId = $pdo->lastInsertId();
                    
                    // Create welcome notification if notification functions exist
                    if (file_exists('notification_functions.php')) {
                        require_once 'notification_functions.php';
                        createNotification($pdo, $userId, 'system_notice', 'Welcome to INTI Reservation System', 'Welcome! Your account has been created and verified. You can now start booking facilities.');
                        printMessage("📬 Welcome notification created", Colors::BLUE);
                    }
                } else {
                    printMessage("❌ Failed to create user: {$userData['username']}", Colors::RED);
                }
            }
        } catch (PDOException $e) {
            printMessage("❌ Database error for user '{$userData['username']}': " . $e->getMessage(), Colors::RED);
        }
    }
}

/**
 * Display user credentials
 */
function displayCredentials() {
    printMessage("\n" . str_repeat("=", 60), Colors::BLUE);
    printMessage("🔑 Default User Credentials", Colors::BLUE, true);
    printMessage(str_repeat("=", 60), Colors::BLUE);
    
    printMessage("👤 Admin User:", Colors::YELLOW, true);
    printMessage("   Email: admin@student.newinti.edu.my", Colors::BLUE);
    printMessage("   Username: admin", Colors::BLUE);
    printMessage("   Password: admin123", Colors::BLUE);
    
    printMessage("\n👤 Test User:", Colors::YELLOW, true);
    printMessage("   Email: test@student.newinti.edu.my", Colors::BLUE);
    printMessage("   Username: testuser", Colors::BLUE);
    printMessage("   Password: test123", Colors::BLUE);
    
    printMessage("\n" . str_repeat("=", 60), Colors::BLUE);
    printMessage("🌐 Login URL: http://localhost/dashboard/INTI_Smart_Booking_FYP/login.php", Colors::GREEN);
    printMessage(str_repeat("=", 60), Colors::BLUE);
}

/**
 * Main execution
 */
function main() {
    global $pdo;
    
    printMessage("🎓 INTI Smart Booking System - Default User Creator", Colors::BLUE, true);
    
    try {
        // Test database connection
        if (!isset($pdo)) {
            throw new PDOException("Database connection not established. Please run setup_database.php first.");
        }
        
        // Test connection
        $pdo->query("SELECT 1");
        printMessage("✅ Database connection successful", Colors::GREEN);
        
        // Create default users
        createDefaultUser($pdo);
        
        // Display credentials
        displayCredentials();
        
        printMessage("\n🎉 Default users created successfully!", Colors::GREEN, true);
        printMessage("You can now log in using the credentials above.", Colors::GREEN);
        
    } catch (PDOException $e) {
        printMessage("❌ Database connection failed: " . $e->getMessage(), Colors::RED);
        printMessage("💡 Please run the database setup first: php setup_database.php", Colors::YELLOW);
        exit(1);
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    // Command line execution
    main();
} else {
    // Web browser execution
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Default User - INTI Smart Booking</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 800px; 
                margin: 0 auto; 
                padding: 20px; 
                background-color: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .success { color: green; }
            .error { color: red; }
            .info { color: blue; }
            .warning { color: orange; }
            pre { 
                background: #f8f9fa; 
                padding: 15px; 
                border-radius: 5px; 
                border-left: 4px solid #007bff;
            }
            .btn {
                background: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }
            .btn:hover { background: #0056b3; }
            .credentials {
                background: #e8f4f8;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #17a2b8;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🎓 INTI Smart Booking - Create Default User</h1>
            
            <?php if (!isset($_POST['create_users'])): ?>
                <p>This tool will create default users for testing and administration.</p>
                <div class="credentials">
                    <h3>🔑 Default Users to be Created:</h3>
                    <p><strong>Admin User:</strong><br>
                    Email: admin@student.newinti.edu.my<br>
                    Username: admin<br>
                    Password: admin123</p>
                    
                    <p><strong>Test User:</strong><br>
                    Email: test@student.newinti.edu.my<br>
                    Username: testuser<br>
                    Password: test123</p>
                </div>
                
                <form method="post">
                    <button type="submit" name="create_users" class="btn">Create Default Users</button>
                </form>
            <?php else: ?>
                <h2>Setup Results:</h2>
                <div style="font-family: monospace;">
                    <?php
                    ob_start();
                    main();
                    $output = ob_get_contents();
                    ob_end_clean();
                    echo $output;
                    ?>
                </div>
                <p><a href="login.php" class="btn">Go to Login Page</a></p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>