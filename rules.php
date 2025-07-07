<?php

include "includes/lang_loader.php";

//rules
// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
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
    <style>
         body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .main-content {
            padding: 2rem;
        }
        .rules-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .rules-card h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .rules-list {
            list-style-type: none;
            padding: 0;
        }
        .rules-list li {
            padding: 1rem 0;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            align-items: flex-start;
        }
        .rules-list li i {
            color: #4285F4;
            margin-right: 1rem;
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }
        .rules-list ul {
            margin-left: 2rem;
            list-style-type: disc;
            padding-left: 0;
        }
        .rules-list ul li {
            border-bottom: none;
            padding: 0.5rem 0;
            display: list-item;
        }
        .rule-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px 12px 0 0;
            margin: -1.5rem -1.5rem 1.5rem;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
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
        <div class="col-md-9 col-lg-10 p-4 main-content">
        <div class="rules-card">
            <div class="rule-header">
                <h4 class="mb-0"><i class="fas fa-book me-2"></i>Reservation System Rules & Regulations</h4>
            </div>
            <ul class="rules-list">
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub1']; ?></strong>
                    <ul>
                        <li><?php echo $text['text1']; ?></li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub2']; ?></strong>
                    <ul>
                        <li><?php echo $text['text2.1']; ?></li>
                        <li><?php echo $text['text2.2']; ?></li>
                        <li><?php echo $text['text2.3']; ?></li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub3']; ?></strong>
                    <ul>
                        <li><?php echo $text['text3']; ?></li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub4']; ?>y</strong>
                    <ul>
                        <li><?php echo $text['text4.1']; ?></li>
                        <li><?php echo $text['text4.2']; ?></li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub5']; ?></strong>
                    <ul>
                        <li><?php echo $text['text5.1']; ?></li>
                        <li><?php echo $text['text5.2']; ?></li>
                        <li><?php echo $text['text5.3']; ?></li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong><?php echo $text['sub6']; ?></strong>
                    <ul>
                        <li><?php echo $text['text6']; ?></li>
                    </ul>
                </li>
            </ul>
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