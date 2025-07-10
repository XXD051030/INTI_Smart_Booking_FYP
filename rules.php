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
    <title>Rules and Regulations - Reservation System</title>
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
                        <a class="nav-link" href="setting.php">
                            <i class="fas fa-cog"></i> <?php echo $text['settings']; ?>
                        </a>
                    </div>
                    <div class="nav-item active">
                        <a class="nav-link" href="rules.php">
                            <i class="fas fa-file-alt"></i> <?php echo $text['rules']; ?>
                        </a>
                    </div>
                </div>
            </div>
    
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="profile-section">
                    <h3><i class="fas fa-file-alt me-2"></i>Reservation System Rules & Regulations</h3>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please read and follow these rules for facility reservations.
                                </div>
                                
                                <div class="rules-content">
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub1']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text1']; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub2']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text2.1']; ?></li>
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text2.2']; ?></li>
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text2.3']; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub3']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text3']; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub4']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text4.1']; ?></li>
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text4.2']; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub5']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text5.1']; ?></li>
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text5.2']; ?></li>
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text5.3']; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="rule-item mb-4">
                                        <h5><i class="fas fa-check-circle text-success me-2"></i><?php echo $text['sub6']; ?></h5>
                                        <ul class="list-unstyled ms-4">
                                            <li><i class="fas fa-arrow-right text-primary me-2"></i><?php echo $text['text6']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>