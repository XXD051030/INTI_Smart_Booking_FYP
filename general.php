<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
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
                        1
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <span><?php echo $user_initial; ?></span>
                    </div>
                    <span class="ms-2 me-3"><?php echo $username; ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="nav flex-column">
                    <div class="nav-item active">
                        <a class="nav-link" href="#">
                            <i class="fas fa-home"></i> General
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="far fa-calendar"></i> Calendar
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-book"></i> Booking
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-bell"></i> Notification
                            <span class="notification-badge">1</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-file-alt"></i> Rules and Regulations
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Profile Section -->
                <div class="profile-section d-flex">
                    <div class="profile-pic">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3><?php echo $username; ?></h3>
                        <p class="mb-1">Email: <?php echo $email; ?></p>
                        <p class="mb-1">Status: No appointment</p>
                        <p class="mb-0">Credit: <span class="text-success">Good <img src="images/assets/green_tick.png" alt="Good" width="20"></span></p>
                    </div>
                </div>
                
                <!-- Reservation Alert -->
                <div class="alert-reservation d-flex align-items-center">
                    <div class="clock-icon">
                        <i class="far fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-2">You have Room 3 reserved at 2PM today.</h5>
                        <div>
                            <button class="btn btn-outline-primary me-2">View Details</button>
                            <button class="btn btn-danger">Cancel</button>
                        </div>
                    </div>
                </div>
                
                <!-- Places Section -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="place-card card">
                            <img src="images/place/discussion_room.jpg" alt="Discussion Room">
                            <div class="content">
                                <h3>Discussion Room</h3>
                                <button class="btn-book">
                                    <i class="fas fa-plus me-1"></i> Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="place-card card">
                            <img src="images/place/basketball_court.jpg" alt="Sport Facilities">
                            <div class="content">
                                <h3>Sport Facilities</h3>
                                <button class="btn-book">
                                    <i class="fas fa-plus me-1"></i> Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="place-card card">
                            <img src="images/place/stem_lab.jpg" alt="STEM Lab">
                            <div class="content">
                                <h3>STEM Lab</h3>
                                <button class="btn-book">
                                    <i class="fas fa-plus me-1"></i> Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 