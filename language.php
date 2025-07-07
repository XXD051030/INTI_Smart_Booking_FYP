<?php
include "includes/lang_loader.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['language'] = $_POST['language'] ?? 'en';
    header('Location: language.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="<?php echo $current; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text['title']; ?></title>
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
            <div class="col-md-9 col-lg-10 p-5">
                <h3><?php echo $text['select_title']; ?></h3>
                <form method="post" class="mt-3" style="max-width: 400px;">
                    <div class="form-group mb-3">
                        <label for="language"><?php echo $text['label']; ?></label>
                        <select class="form-control" id="language" name="language">
                            <option value="en" <?php if ($current === 'en') echo 'selected'; ?>>🇺🇸 English</option>
                            <option value="ms" <?php if ($current === 'ms') echo 'selected'; ?>>🇲🇾 Malay (Bahasa Melayu)</option>
                            <option value="zh" <?php if ($current === 'zh') echo 'selected'; ?>>🇨🇳 Chinese (中文)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $text['save']; ?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
