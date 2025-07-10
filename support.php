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
    <title>Reservation Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-card {
        background: #ecf0f5;
        border-radius: 51px;
        padding: 40px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: left;
        font-family: 'Roboto', sans-serif;
        }

        .profile-card h3 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
        border-bottom: 2px solid #d0d4d9;
        padding-bottom: 0.5rem;
        }

        .profile-card p {
        font-size: 1.05rem;
        color: #444;
        line-height: 1.6;
        margin-bottom: 0.5rem;
        }

        .profile-card a {
        color: #0069d9;
        text-decoration: none;
        font-weight: 500;
        }

        .profile-card a:hover {
        text-decoration: underline;
        }

        .profile-card .btn {
        font-size: 1rem;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        border-radius: 10px;
        color: white;
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
                <h1 class="mb-4"><?php echo $text['Support Page']; ?></h1>
                <div class="profile-card">
                <div class="mb-5">
                    <h3><?php echo $text['Contact Information']; ?></h3>
                    <p><?php echo $text['Website']; ?><a href>https://www.newinti.edu.my</a></p>
                    <p><?php echo $text['email']; ?> iicp.adco@newinti.edu.my</p>
                    <p><?php echo $text['Phone']; ?> +04-631 0138</p>
                    <p><?php echo $text['Address']; ?> 1-Z Lebuh Bukit Jambul 11900 Penang, Malaysia</p>
                    <a class="btn btn-success mt-3" href="mailto:iicp.adco@inti.edu.my?subject=Support%20Request&body=Please%20describe%20your%20issue%20here."><?php echo $text['Click here to Email Support']; ?></a>
                </div>
                </div>
            </div>
        </div>    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
