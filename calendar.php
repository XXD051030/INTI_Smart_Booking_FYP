<?php
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
                <h3 class="mb-4"><i class="far fa-calendar me-2"></i>Reservation Calendar</h3>
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
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: 'get_bookings.php'
            });
            calendar.render();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
