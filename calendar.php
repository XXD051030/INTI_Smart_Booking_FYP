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
    <title>Calendar - Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <style>
        .main-content {
            padding: 2rem;
        }
        .header {
            background: #fff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar {
            background: #f8f9fa;
            padding: 1rem;
            height: 100%;
        }
        @media (max-width: 768px) {
            .sidebar {
            width: 100%;
            height: auto;
            position: relative;
            }
            .main-content {
            padding-top: 60px;
            }
        }
        #calendar {
            background-color: #ecf0f5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .fc-toolbar.fc-header-toolbar {
        background-color: #c1bfbf;
        padding: 0;
        margin: 0;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 50px;
        }

        .fc-toolbar-title {
        color: #333;
        font-weight: 700;
        font-size: 1.5rem;
        margin: 0;
        line-height: 50px;
        }

        .fc-scrollgrid,
        .fc-scrollgrid-section-header,
        .fc-scrollgrid-section-body,
        .fc-scrollgrid table {
        margin: 0 !important;
        border: none !important;
        padding: 0 !important;
        border-collapse: collapse !important;
        }

        .fc-scrollgrid-section-header + .fc-scrollgrid-section-body {
        border-top: none !important;
        }

        .fc-col-header-cell {
        background-color: #ecf0f5;
        height: 60px;
        padding: 0;
        text-align: center;
        vertical-align: middle;
        }

        .fc-col-header-cell-cushion {
        color: black;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1rem;
        line-height: 60px;
        margin: 0;
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
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                    <span><?php echo $user_initial; ?></span>
                </div>
                <span class="ms-2 me-3"><?php echo $username; ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $text['logout']; ?>
                </a>
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
                    <div class="nav-item active">
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
                    <div class="nav-item">
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
            <div class="col-md-9 col-lg-10 main-content">
                <h3 class="mb-4"><i class="far fa-calendar me-2"></i>Calendar</h3>
                <div id='calendar'></div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                left: '',
                center: 'title',
                right: ''
                },
                events: 'get_bookings.php'
            });
            calendar.render();
            });
        </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
