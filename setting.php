<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

// Get user information from database
try {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not found in database, logout
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    $username = htmlspecialchars($user['username']);
    $email = htmlspecialchars($user['email']);
    $user_initial = strtoupper(substr($username, 0, 1));
} catch (PDOException $e) {
    // Database error, redirect to login
    header('Location: login.php');
    exit;
}
include "includes/lang_loader.php"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Dashboard - Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main-content {
            margin-left: 250px;
            padding: 80px 20px 20px;
        }
        .settings-card h5 {
            margin-bottom: 15px;
            color: #333;
        }
        .settings-card .form-group {
            margin-bottom: 15px;
        }
        .settings-card .form-control {
            border-radius: 5px;
        }
        .settings-card .btn-logout {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
        }
        .settings-card .btn-logout:hover {
            background-color: #c82333;
        }
        .settings-card .btn-save {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .settings-card .btn-save:hover {
            background-color: #0056b3;
        }
        .profile-card {
            background: #ecf0f5;
            border-radius: 51px;
            padding: 40px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: left;
        }
        .profile-card h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .profile-card .user-info {
            margin-bottom: 20px;
        }
        .profile-card .user-info i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .profile-card .credit-circle {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            font-weight: 500;
            margin: 0 auto 20px;
        }
        .fas.fa-user{
            font-size: 20px; 
        }   
        hr {
            border: none;
            border-top: 1px solid #000000;
            margin: 10;
        }
        .icon {
            font-size: 20px;
            width: 24px;
            display: inline-block;
            text-align: center;
            margin-right: 20px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .header {
                width: 100%;
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="header">
            <div class="d-flex align-items-center">
                <img src="images/logo/inti_logo.png" alt="INTI Logo" height="40">
                <h2 class="ms-3 mb-0">Reservation Dashboard</h2>
            </div>
            <div class="d-flex align-items-center">
                <div class="position-relative me-3">
                    <i class="fas fa-bell fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <span><?php echo $user_initial; ?></span>
                    </div>
                    <span class="ms-2 me-3"><?php echo $username; ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $text['logout']; ?>
                    </a>
                </div> 
            </div>
        </div>
        
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="nav flex-column">
                    <div class="nav-item">
                        <a class="nav-link" href="general.php">
                            <i class="fas fa-home"></i> <?php echo $text['general']; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="calendar.php">
                            <i class="far fa-calendar"></i> <?php echo $text['calendar']; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="booking.php">
                            <i class="fas fa-book"></i> <?php echo $text['booking']; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="my_bookings.php">
                            <i class="fas fa-book"></i> <?php echo $text['mybk']; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-bell"></i> <?php echo $text['notification']; ?>
                            <span class="notification-badge">1</span>
                        </a>
                    </div>
                    <div class="nav-item active">
                        <a class="nav-link" href="setting.php">
                            <i class="fas fa-cog"></i> <?php echo $text['settings']; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="rules.php">
                            <i class="fas fa-file-alt"></i> <?php echo $text['rules']; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h3>  Settings</h3>
                <div class="profile-card">
                    <div class="user-info">
                    <!-- -->   
                    <div class="setting-box" onclick="window.location.href='profile.php'" style="cursor:pointer;">
                        <h5><i class="fas fa-user icon me-3"></i>
                        <span>&nbsp;&nbsp;<?php echo $text['profile']; ?></span></h5>
                    </div>
                    <hr />
                    
                    <div class="setting-box" onclick="window.location.href='language.php'" style="cursor:pointer;">
                        <h5><i class="fas fa-language icon"></i>
                        <span>&nbsp;</span><?php echo $text['Lang']; ?></h5>
                    </div>
                    <hr />

                    <div class="setting-box" onclick="window.location.href='support.php'" style="cursor:pointer;">
                        <h5><i class="fas fa-question icon"></i>
                        <span>&nbsp;<?php echo $text['Sup']; ?></span></h5>
                    </div>
                    <hr />
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 