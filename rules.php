<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
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
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .main-content {
            margin-left: 260px;
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
    <!-- Sidebar -->
    <div class="sidebar position-fixed">
        <img src="../images/logo/inti_logo.png" alt="INTI Logo" class="logo">
        <div class="nav flex-column">
            <div class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            <div class="nav-item active">
                <a class="nav-link" href="rules_regulations.php">
                    <i class="fas fa-book"></i> Rules & Regulations
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="otps.php">
                    <i class="fas fa-key"></i> OTP Verification
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Admin Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Rules and Regulations</h3>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="rules-card">
            <div class="rule-header">
                <h4 class="mb-0"><i class="fas fa-book me-2"></i>Reservation System Rules & Regulations</h4>
            </div>
            <ul class="rules-list">
                <li><i class="fas fa-check-circle"></i><strong>1. Booking Eligibility</strong>
                    <ul>
                        <li>Only registered INTI students and staff are allowed to use the booking system.</li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong>2. Booking Time Limits</strong>
                    <ul>
                        <li>Each booking session is limited to a maximum of 2 hours.</li>
                        <li>Users are allowed to make up to 2 bookings per day.</li>
                        <li>Bookings can be made up to 3 days in advance.</li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong>3. Check-in Policy</strong>
                    <ul>
                        <li>Users must check in within 15 minutes after the booking start time. Failure to check in will result in automatic cancellation of the booking.</li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong>4. Cancellation Policy</strong>
                    <ul>
                        <li>Cancellations must be made at least 15 minutes before the scheduled time.</li>
                        <li>Users who fail to show up 3 times or more may have their booking privileges temporarily suspended.</li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong>5. Code of Conduct</strong>
                    <ul>
                        <li>Users must maintain cleanliness and respect the facility rules.</li>
                        <li>Loud behavior, misuse of space, or any form of disturbance is strictly prohibited.</li>
                        <li>Any damage caused to the facilities will be the responsibility of the user.</li>
                    </ul>
                </li>
                <li><i class="fas fa-check-circle"></i><strong>6. Admin Rights</strong>
                    <ul>
                        <li>The admin has the right to override, cancel or block any booking if necessary (e.g., for maintenance or misuse).</li>
                    </ul>
                </li>
            </ul>
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