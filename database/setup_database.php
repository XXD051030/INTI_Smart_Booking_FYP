<?php
/**
 * INTI Smart Booking System - Database Setup Script
 * 
 * This script automatically sets up the complete database structure
 * for the INTI Student Registration & Facility Booking System
 * 
 * Version: 1.0
 * Author: System Administrator
 * Date: 2024
 */

// ANSI color codes for terminal output
class Colors {
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const GRAY = "\033[90m";
    const BOLD = "\033[1m";
    const RESET = "\033[0m";
}

// Configuration
$config = [
    'host' => 'localhost',
    'root_user' => 'root',
    'root_password' => '', // XAMPP default has no root password
    'db_name' => 'reservation_system',
    'app_user' => 'webapp',
    'app_password' => 'webapp123'
];

// SQL files to import (order matters due to foreign keys)
$sql_files = [
    'create_users_table.sql',
    'create_facilities_table.sql', 
    'create_bookings_table.sql',
    'create_otp_table.sql',
    'create_notifications_table.sql'
];

/**
 * Print colored output to terminal
 */
function printMessage($message, $color = Colors::RESET, $bold = false) {
    $prefix = $bold ? Colors::BOLD : '';
    echo $prefix . $color . $message . Colors::RESET . "\n";
}

/**
 * Print section header
 */
function printHeader($title) {
    echo "\n" . str_repeat("=", 60) . "\n";
    printMessage($title, Colors::BLUE, true);
    echo str_repeat("=", 60) . "\n";
}

/**
 * Connect to MySQL as root
 */
function connectAsRoot($config) {
    try {
        $dsn = "mysql:host={$config['host']}";
        $pdo = new PDO($dsn, $config['root_user'], $config['root_password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        printMessage("❌ Failed to connect to MySQL as root: " . $e->getMessage(), Colors::RED);
        exit(1);
    }
}

/**
 * Create database and user
 */
function setupDatabaseAndUser($pdo, $config) {
    printHeader("🗄️  Setting Up Database and User");
    
    try {
        // Drop existing database if it exists
        printMessage("🗑️  Dropping existing database: {$config['db_name']}...", Colors::YELLOW);
        $pdo->exec("DROP DATABASE IF EXISTS {$config['db_name']}");
        printMessage("✅ Existing database dropped successfully", Colors::GREEN);
        
        // Create fresh database
        printMessage("📊 Creating new database: {$config['db_name']}...", Colors::YELLOW);
        $pdo->exec("CREATE DATABASE {$config['db_name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        printMessage("✅ Database created successfully", Colors::GREEN);
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT User FROM mysql.user WHERE User = ? AND Host = 'localhost'");
        $stmt->execute([$config['app_user']]);
        $userExists = $stmt->fetch();
        
        if (!$userExists) {
            // Create user
            printMessage("👤 Creating user: {$config['app_user']}...", Colors::YELLOW);
            $pdo->exec("CREATE USER '{$config['app_user']}'@'localhost' IDENTIFIED BY '{$config['app_password']}'");
            printMessage("✅ User created successfully", Colors::GREEN);
        } else {
            printMessage("ℹ️  User '{$config['app_user']}' already exists", Colors::YELLOW);
        }
        
        // Grant privileges
        printMessage("🔑 Granting privileges...", Colors::YELLOW);
        $pdo->exec("GRANT ALL PRIVILEGES ON {$config['db_name']}.* TO '{$config['app_user']}'@'localhost'");
        $pdo->exec("FLUSH PRIVILEGES");
        printMessage("✅ Privileges granted successfully", Colors::GREEN);
        
    } catch (PDOException $e) {
        printMessage("❌ Database setup failed: " . $e->getMessage(), Colors::RED);
        exit(1);
    }
}

/**
 * Connect to the application database
 */
function connectToAppDatabase($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['app_user'], $config['app_password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        printMessage("❌ Failed to connect to application database: " . $e->getMessage(), Colors::RED);
        exit(1);
    }
}

/**
 * Import SQL files
 */
function importSQLFiles($pdo, $sql_files) {
    printHeader("📋 Importing Table Structures");
    
    foreach ($sql_files as $file) {
        if (!file_exists($file)) {
            printMessage("❌ SQL file not found: $file", Colors::RED);
            continue;
        }
        
        printMessage("📄 Importing: $file...", Colors::YELLOW);
        
        try {
            $sql = file_get_contents($file);
            
            // Clean up SQL content line by line for better parsing
            $lines = explode("\n", $sql);
            $cleanedLines = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip empty lines and comments
                if (empty($line) || strpos($line, '--') === 0) {
                    continue;
                }
                
                // Skip CREATE DATABASE and USE statements
                if (preg_match('/^CREATE\s+DATABASE/i', $line) || 
                    preg_match('/^USE\s+/i', $line)) {
                    printMessage("  ⏭️  Skipping: " . substr($line, 0, 50) . "...", Colors::GRAY);
                    continue;
                }
                
                $cleanedLines[] = $line;
            }
            
            // Rejoin and split by semicolon for proper statement separation
            $cleanedSQL = implode("\n", $cleanedLines);
            $statements = explode(';', $cleanedSQL);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                // Show what we're executing
                $preview = str_replace(["\n", "\r", "\t"], " ", substr($statement, 0, 80));
                printMessage("  🔧 Executing: " . $preview . "...", Colors::GRAY);
                
                try {
                    $result = $pdo->exec($statement);
                    printMessage("  ✅ Executed successfully", Colors::GREEN);
                } catch (PDOException $e) {
                    printMessage("  ❌ Statement failed: " . $e->getMessage(), Colors::RED);
                    printMessage("  📄 Failed SQL: " . substr($statement, 0, 200) . "...", Colors::GRAY);
                    throw $e;
                }
            }
            
            printMessage("✅ $file imported successfully", Colors::GREEN);
            
        } catch (PDOException $e) {
            printMessage("❌ Failed to import $file: " . $e->getMessage(), Colors::RED);
            // Continue with other files even if one fails
        }
    }
}

/**
 * Create default users for testing
 */
function createDefaultUsers($pdo) {
    printHeader("👤 Creating Default Users");
    
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
    
    foreach ($defaultUsers as $userData) {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$userData['email']]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                if ($existingUser['is_verified'] == 1) {
                    printMessage("ℹ️  User '{$userData['username']}' already exists and is verified", Colors::YELLOW);
                } else {
                    // Update existing unverified user
                    $password_hash = password_hash($userData['password'], PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, is_verified = ? WHERE email = ?");
                    
                    if ($updateStmt->execute([$userData['username'], $password_hash, $userData['is_verified'], $userData['email']])) {
                        printMessage("✅ Updated user: {$userData['username']}", Colors::GREEN);
                    }
                }
            } else {
                // Create new user
                $password_hash = password_hash($userData['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_verified, created_at) VALUES (?, ?, ?, ?, NOW())");
                
                if ($stmt->execute([$userData['username'], $userData['email'], $password_hash, $userData['is_verified']])) {
                    printMessage("✅ Created user: {$userData['username']}", Colors::GREEN);
                    
                    // Create welcome notification
                    $userId = $pdo->lastInsertId();
                    try {
                        $notificationStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (?, 'system_notice', 'Welcome to INTI Reservation System', 'Welcome! Your account has been created and verified. You can now start booking facilities.', NOW())");
                        $notificationStmt->execute([$userId]);
                    } catch (Exception $e) {
                        // Notification creation is optional
                    }
                }
            }
        } catch (PDOException $e) {
            printMessage("❌ Error creating user '{$userData['username']}': " . $e->getMessage(), Colors::RED);
        }
    }
}

/**
 * Verify database setup
 */
function verifySetup($pdo, $config) {
    printHeader("🔍 Verifying Database Setup");
    
    try {
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $expected_tables = ['users', 'facilities', 'bookings', 'user_otp', 'notifications'];
        $missing_tables = array_diff($expected_tables, $tables);
        
        if (empty($missing_tables)) {
            printMessage("✅ All tables created successfully", Colors::GREEN);
            
            // Show table counts
            foreach ($expected_tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                printMessage("📊 Table '$table': $count records", Colors::BLUE);
            }
        } else {
            printMessage("❌ Missing tables: " . implode(', ', $missing_tables), Colors::RED);
        }
        
        // Test the connection that the application will use
        printMessage("🔗 Testing application database connection...", Colors::YELLOW);
        include_once 'db.php';
        printMessage("✅ Application database connection successful", Colors::GREEN);
        
    } catch (Exception $e) {
        printMessage("❌ Verification failed: " . $e->getMessage(), Colors::RED);
    }
}

/**
 * Main execution
 */
function main() {
    global $config, $sql_files;
    
    printMessage("🎓 INTI Smart Booking System - Database Setup", Colors::BLUE, true);
    printMessage("⚠️  WARNING: This will DELETE the existing database!", Colors::RED, true);
    printMessage("Setting up database: {$config['db_name']}", Colors::BLUE);
    printMessage("Target user: {$config['app_user']}", Colors::BLUE);
    
    // Step 1: Connect as root and setup database/user
    $root_pdo = connectAsRoot($config);
    setupDatabaseAndUser($root_pdo, $config);
    
    // Step 2: Connect as application user
    printHeader("🔌 Connecting as Application User");
    $app_pdo = connectToAppDatabase($config);
    printMessage("✅ Connected successfully as {$config['app_user']}", Colors::GREEN);
    
    // Step 3: Import SQL files
    importSQLFiles($app_pdo, $sql_files);
    
    // Step 4: Create default users
    createDefaultUsers($app_pdo);
    
    // Step 5: Verify setup
    verifySetup($app_pdo, $config);
    
    // Success message
    printHeader("🎉 Setup Complete!");
    printMessage("Database has been completely reset and setup successfully!", Colors::GREEN, true);
    printMessage("All previous data has been deleted and replaced with fresh data.", Colors::YELLOW);
    printMessage("Your INTI Smart Booking System is now ready to use.", Colors::GREEN);
    printMessage("You can now access the application in your web browser.", Colors::BLUE);
    
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
    printMessage("Database Configuration:", Colors::YELLOW, true);
    printMessage("• Database: {$config['db_name']}", Colors::BLUE);
    printMessage("• Username: {$config['app_user']}", Colors::BLUE);
    printMessage("• Password: {$config['app_password']}", Colors::BLUE);
    printMessage("• Host: {$config['host']}", Colors::BLUE);
    
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
    printMessage("Default User Credentials:", Colors::YELLOW, true);
    printMessage("👤 Admin User:", Colors::GREEN, true);
    printMessage("  Email: admin@student.newinti.edu.my", Colors::BLUE);
    printMessage("  Username: admin", Colors::BLUE);
    printMessage("  Password: admin123", Colors::BLUE);
    
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
    printMessage("👤 Test User:", Colors::GREEN, true);
    printMessage("  Email: test@student.newinti.edu.my", Colors::BLUE);
    printMessage("  Username: testuser", Colors::BLUE);
    printMessage("  Password: test123", Colors::BLUE);
    
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
    printMessage("🌐 Login URL: http://localhost/dashboard/INTI_Smart_Booking_FYP/login.php", Colors::GREEN);
    echo php_sapi_name() === 'cli' ? "\n" : "<br>";
}

// Run the setup
if (php_sapi_name() === 'cli') {
    main();
} else {
    // If accessed via web browser, show HTML version
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .success { color: green; }
            .error { color: red; }
            .info { color: blue; }
            .warning { color: orange; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>🎓 INTI Smart Booking System - Database Setup</h1>
        
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3 style="color: #856404; margin-top: 0;">⚠️ WARNING - Complete Database Reset</h3>
            <p style="color: #856404; margin-bottom: 0;">
                <strong>This will completely DELETE the existing database and all data!</strong><br>
                • All user accounts will be removed<br>
                • All booking records will be lost<br>
                • All notifications will be deleted<br>
                <strong>Only proceed if you want to start fresh!</strong>
            </p>
        </div>
        
        <p>Please run this script from the command line for the best experience:</p>
        <pre>php setup_database.php</pre>
        <p>Or click the button below to run the complete database reset:</p>
        <form method="post">
            <button type="submit" name="run_setup" style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px;" onclick="return confirm('Are you sure you want to DELETE the entire database and start fresh? This action cannot be undone!')">
                🗑️ Reset Database (DELETE ALL DATA)
            </button>
        </form>
        
        <?php
        if (isset($_POST['run_setup'])) {
            echo "<h2>Setup Results:</h2>";
            echo "<pre>";
            ob_start();
            main();
            $output = ob_get_contents();
            ob_end_clean();
            // Remove ANSI codes for web display
            $output = preg_replace('/\033\[[0-9;]*m/', '', $output);
            echo htmlspecialchars($output);
            echo "</pre>";
        }
        ?>
    </body>
    </html>
    <?php
}
?>