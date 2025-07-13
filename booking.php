<?php
session_start();
require_once 'db.php';
require_once 'notification_functions.php';

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
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    $username = htmlspecialchars($user['username']);
    $email = htmlspecialchars($user['email']);
    $user_initial = strtoupper(substr($username, 0, 1));
    
    // Get notification count
    $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
} catch (PDOException $e) {
    header('Location: login.php');
    exit;
}

// Get all active facilities
try {
    $stmt = $pdo->prepare("SELECT * FROM facilities WHERE is_active = 1 ORDER BY type, name");
    $stmt->execute();
    $facilities = $stmt->fetchAll();
} catch (PDOException $e) {
    $facilities = [];
}
include "includes/lang_loader.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Facilities - INTI Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/booking.css">
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="header">
            <div class="d-flex align-items-center">
                <img src="images/logo/inti_logo.png" alt="INTI Logo" height="40">
                <h2 class="ms-3 mb-0">Book Facilities</h2>
            </div>
            <div class="d-flex align-items-center">
                <div class="position-relative me-3">
                    <i class="fas fa-bell fs-4 notification-icon" id="notification-icon"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                        <?php echo $unread_count; ?>
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
                    <div class="nav-item active">
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
                    <div class="nav-item">
                        <a class="nav-link" href="rules.php">
                            <i class="fas fa-file-alt"></i> <?php echo $text['rules']; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Booking Alert -->
                <div id="booking-alert" class="alert alert-info d-none">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="alert-message"></span>
                </div>

                <div class="row">
                    <!-- Left Panel: Facility Selection -->
                    <div class="col-lg-4">
                        <div class="facility-section">
                            <h4 class="section-title">
                                <i class="fas fa-building me-2"></i><?php echo $text['select']; ?>
                            </h4>
                            
                            <!-- Facility Type Filter -->
                            <div class="facility-filter mb-4">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">
                                        <?php echo $text['all']; ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="discussion_room">
                                        <?php echo $text['dis']; ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="basketball_court">
                                        <?php echo $text['sport']; ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="stem_lab">
                                        <?php echo $text['stem1']; ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Facility Cards -->
                            <div class="facility-list">
                                <?php foreach ($facilities as $facility): ?>
                                <div class="facility-card" 
                                     data-facility-id="<?php echo $facility['facility_id']; ?>"
                                     data-facility-type="<?php echo $facility['type']; ?>"
                                     data-advance-days="<?php echo $facility['advance_booking_days']; ?>">
                                    <div class="facility-image">
                                        <img src="<?php echo htmlspecialchars($facility['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($facility['name']); ?>">
                                    </div>
                                    <div class="facility-info">
                                        <h5><?php echo htmlspecialchars($facility['name']); ?></h5>
                                        <p class="facility-details">
                                            <i class="fas fa-users me-1"></i> <?php echo $text['capa']; ?> <?php echo $facility['capacity']; ?>
                                        </p>
                                        <p class="facility-details">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($facility['location']); ?>
                                        </p>
                                        <p class="facility-booking-rule">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php if ($facility['advance_booking_days'] == 0): ?>
                                                <?php echo $text['bookday1']; ?>
                                            <?php else: ?>
                                                <?php echo $text['bk']; ?> <?php echo $facility['advance_booking_days']; ?> <?php echo $text['bk1']; ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Booking Interface -->
                    <div class="col-lg-8">
                        <div class="booking-section">
                            <!-- Selected Facility Display -->
                            <div id="selected-facility" class="selected-facility-info d-none">
                                <h4 class="section-title">
                                    <i class="fas fa-calendar-check me-2"></i>Book <span id="selected-facility-name"></span>
                                </h4>
                                <div class="facility-summary">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong><?php echo $text['capa']; ?></strong> <span id="selected-facility-capacity"></span> <?php echo $text['ppl']; ?></p>
                                            <p><strong><?php echo $text['locat']; ?></strong> <span id="selected-facility-location"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong><?php echo $text['Hours']; ?></strong> 08:00 - 17:00</p>
                                            <p><strong><?php echo $text['bookingrule']; ?></strong> <span id="selected-facility-rule"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Selection -->
                            <div id="date-selection" class="date-section d-none">
                                <h5><?php echo $text['selectdate']; ?></h5>
                                <div class="date-input-group">
                                    <input type="date" id="booking-date" class="form-control" min="">
                                    <small class="form-text text-muted">
                                        <?php echo $text['bd1']; ?> <span id="max-days-text">0</span> <?php echo $text['bd1.1']; ?>
                                    </small>
                                    <div class="alert alert-info mt-2 d-none" id="date-help-alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong><?php echo $text['note']; ?></strong> <?php echo $text['notesub']; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Slot Selection -->
                            <div id="time-selection" class="time-section d-none">
                                <h5><?php echo $text['selecttime']; ?></h5>
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong><?php echo $text['mulbk']; ?></strong> <?php echo $text['timeslotselect']; ?> 
                                    <?php echo $text['timeslotselect1']; ?>
                                </div>
                                <div class="time-grid" id="time-grid">
                                    <!-- Time slots will be generated by JavaScript -->
                                </div>
                                <div class="time-legend mt-3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="legend-item">
                                            <span class="legend-color available"></span>
                                            <small><?php echo $text['ava']; ?></small>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-color booked"></span>
                                            <small><?php echo $text['booked']; ?></small>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-color selected"></span>
                                            <small><?php echo $text['selected']; ?></small>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-color disabled"></span>
                                            <small><?php echo $text['unava']; ?></small>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #f8f9fa; border-color: #dee2e6; opacity: 0.6;"></span>
                                            <small><?php echo $text['noncon']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Booking Form -->
                            <div id="booking-form-section" class="booking-form d-none">
                                <h5><?php echo $text['bkdetails']; ?></h5>
                                <form id="booking-form">
                                    <div class="mb-3">
                                        <label for="booking-purpose" class="form-label"><?php echo $text['purpose']; ?></label>
                                        <textarea class="form-control" id="booking-purpose" rows="3" 
                                                placeholder="Please describe the purpose of your booking (minimum 10 characters)"
                                                maxlength="500" required></textarea>
                                        <div class="form-text">
                                            <span id="char-count">0</span>/<?php echo $text['500']; ?>
                                        </div>
                                    </div>

                                    <!-- Booking Summary -->
                                    <div class="booking-summary mb-4">
                                        <h6><?php echo $text['bksum']; ?></h6>
                                        <div class="summary-content">
                                            <p><strong><?php echo $text['fa']; ?></strong> <span id="summary-facility">-</span></p>
                                            <p><strong><?php echo $text['date']; ?></strong> <span id="summary-date">-</span></p>
                                            <p><strong><?php echo $text['time']; ?></strong> <span id="summary-time">-</span></p>
                                            <p><strong><?php echo $text['dura']; ?></strong> <span id="summary-duration"><?php echo $text['1hour']; ?></span></p>
                                        </div>
                                    </div>

                                    <!-- Daily Booking Limit Notice -->
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong><?php echo $text['daylimit']; ?></strong> <?php echo $text['daylimit1']; ?>
                                        <span id="daily-booking-count"></span>
                                    </div>

                                    <div class="booking-actions">
                                        <button type="button" class="btn btn-secondary me-2" id="reset-booking">
                                            <i class="fas fa-undo me-1"></i> <?php echo $text['reset']; ?>
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="confirm-booking">
                                            <i class="fas fa-check me-1"></i> <?php echo $text['ocnfirmbk']; ?>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Initial State -->
                            <div id="initial-state" class="text-center py-5">
                                <i class="fas fa-hand-pointer text-muted" style="font-size: 4rem;"></i>
                                <h4 class="text-muted mt-3"><?php echo $text['selectbk']; ?></h4>
                                <p class="text-muted"><?php echo $text['selectbk1']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo $text['load']; ?></span>
                    </div>
                    <div class="mt-3"><?php echo $text['processbk']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/booking.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html> 