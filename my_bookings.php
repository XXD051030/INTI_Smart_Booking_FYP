<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

// Get user information
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
} catch (PDOException $e) {
    header('Location: login.php');
    exit;
}

// Pagination settings
$bookings_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $bookings_per_page;

// Filter settings
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

try {
    // Build query based on filters
    $where_conditions = ["b.user_id = ?"];
    $params = [$_SESSION['user_id']];
    
    if ($status_filter !== 'all') {
        $where_conditions[] = "b.status = ?";
        $params[] = $status_filter;
    }
    
    if ($date_filter !== 'all') {
        switch ($date_filter) {
            case 'upcoming':
                $where_conditions[] = "b.booking_date >= CURDATE()";
                break;
            case 'past':
                $where_conditions[] = "b.booking_date < CURDATE()";
                break;
            case 'today':
                $where_conditions[] = "b.booking_date = CURDATE()";
                break;
        }
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count for pagination
    $count_query = "
        SELECT COUNT(*) as total 
        FROM bookings b 
        JOIN facilities f ON b.facility_id = f.facility_id 
        WHERE $where_clause
    ";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_bookings = $stmt->fetch()['total'];
    $total_pages = ceil($total_bookings / $bookings_per_page);

    // Get bookings with pagination
    $bookings_query = "
        SELECT b.*, f.name as facility_name, f.location, f.image_path
        FROM bookings b 
        JOIN facilities f ON b.facility_id = f.facility_id 
        WHERE $where_clause
        ORDER BY b.booking_date DESC, b.start_time DESC 
        LIMIT $bookings_per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($bookings_query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

} catch (PDOException $e) {
    $bookings = [];
    $total_bookings = 0;
    $total_pages = 0;
}

// Helper function to check if booking can be cancelled
function canCancelBooking($booking_date, $start_time) {
    $booking_datetime = $booking_date . ' ' . $start_time;
    $booking_timestamp = strtotime($booking_datetime);
    $current_timestamp = time();
    $time_diff_minutes = ($booking_timestamp - $current_timestamp) / 60;
    
    return $time_diff_minutes > 30; // Can cancel if more than 30 minutes away
}

// Helper function to format time for display
function formatTimeForDisplay($time) {
    return date('g:i A', strtotime($time));
}

// Helper function to format date for display
function formatDateForDisplay($date) {
    return date('l, F j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - INTI Reservation System</title>
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
                <h2 class="ms-3 mb-0">My Bookings</h2>
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
                    <div class="nav-item">
                        <a class="nav-link" href="general.php">
                            <i class="fas fa-home"></i> General
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="far fa-calendar"></i> Calendar
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="booking.php">
                            <i class="fas fa-book"></i> Booking
                        </a>
                    </div>
                    <div class="nav-item active">
                        <a class="nav-link" href="my_bookings.php">
                            <i class="fas fa-history"></i> My Bookings
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
                        <a class="nav-link" href="rules.php">
                            <i class="fas fa-file-alt"></i> Rules and Regulations
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Alert for messages -->
                <div id="alert-container"></div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-calendar-check text-primary fs-2 mb-2"></i>
                                <h5 class="card-title">Total Bookings</h5>
                                <h3 class="text-primary"><?php echo $total_bookings; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-clock text-warning fs-2 mb-2"></i>
                                <h5 class="card-title">Upcoming</h5>
                                <h3 class="text-warning">
                                    <?php
                                    $upcoming_count = 0;
                                    foreach ($bookings as $booking) {
                                        if ($booking['booking_date'] >= date('Y-m-d') && $booking['status'] === 'confirmed') {
                                            $upcoming_count++;
                                        }
                                    }
                                    echo $upcoming_count;
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle text-success fs-2 mb-2"></i>
                                <h5 class="card-title">Completed</h5>
                                <h3 class="text-success">
                                    <?php
                                    $completed_count = 0;
                                    foreach ($bookings as $booking) {
                                        if ($booking['booking_date'] < date('Y-m-d') && $booking['status'] === 'confirmed') {
                                            $completed_count++;
                                        }
                                    }
                                    echo $completed_count;
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-times-circle text-danger fs-2 mb-2"></i>
                                <h5 class="card-title">Cancelled</h5>
                                <h3 class="text-danger">
                                    <?php
                                    $cancelled_count = 0;
                                    foreach ($bookings as $booking) {
                                        if ($booking['status'] === 'cancelled') {
                                            $cancelled_count++;
                                        }
                                    }
                                    echo $cancelled_count;
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-filter me-2"></i>Filter Bookings
                        </h5>
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date Range</label>
                                <select class="form-select" id="date" name="date">
                                    <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                                    <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="my_bookings.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>
                        <i class="fas fa-list me-2"></i>Your Bookings
                        <span class="badge bg-primary ms-2"><?php echo $total_bookings; ?></span>
                    </h4>
                    <a href="booking.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Booking
                    </a>
                </div>

                <!-- Bookings List -->
                <?php if (empty($bookings)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No bookings found</h4>
                            <p class="text-muted">You don't have any bookings matching the selected criteria.</p>
                            <a href="booking.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Make Your First Booking
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $can_cancel = canCancelBooking($booking['booking_date'], $booking['start_time']);
                            $is_past = $booking['booking_date'] < date('Y-m-d');
                            $is_today = $booking['booking_date'] === date('Y-m-d');
                            $status_class = $booking['status'] === 'confirmed' ? 'success' : 'danger';
                            ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 booking-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($booking['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($booking['facility_name']); ?>"
                                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($booking['facility_name']); ?></h5>
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($booking['location']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </div>

                                        <div class="booking-details">
                                            <div class="row">
                                                <div class="col-6">
                                                    <p class="mb-2">
                                                        <i class="fas fa-calendar text-primary me-2"></i>
                                                        <strong>Date:</strong><br>
                                                        <small><?php echo formatDateForDisplay($booking['booking_date']); ?></small>
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-2">
                                                        <i class="fas fa-clock text-primary me-2"></i>
                                                        <strong>Time:</strong><br>
                                                        <small>
                                                            <?php echo formatTimeForDisplay($booking['start_time']); ?> - 
                                                            <?php echo formatTimeForDisplay($booking['end_time']); ?>
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <p class="mb-3">
                                                <i class="fas fa-edit text-primary me-2"></i>
                                                <strong>Purpose:</strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['purpose']); ?></small>
                                            </p>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Booking ID: #<?php echo $booking['booking_id']; ?>
                                                </small>
                                                
                                                <?php if ($booking['status'] === 'confirmed' && $can_cancel && !$is_past): ?>
                                                    <button class="btn btn-outline-danger btn-sm cancel-booking" 
                                                            data-booking-id="<?php echo $booking['booking_id']; ?>"
                                                            data-facility-name="<?php echo htmlspecialchars($booking['facility_name']); ?>"
                                                            data-booking-date="<?php echo formatDateForDisplay($booking['booking_date']); ?>"
                                                            data-booking-time="<?php echo formatTimeForDisplay($booking['start_time']); ?>">
                                                        <i class="fas fa-times me-1"></i> Cancel
                                                    </button>
                                                <?php elseif ($booking['status'] === 'confirmed' && !$can_cancel && !$is_past): ?>
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Cannot cancel (within 30 min)
                                                    </small>
                                                <?php elseif ($is_past && $booking['status'] === 'confirmed'): ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Completed
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_today && $booking['status'] === 'confirmed'): ?>
                                        <div class="card-footer bg-warning text-dark">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <strong>Today's Booking!</strong> Don't forget your appointment.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Bookings pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Cancel Booking
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                    <div class="cancel-booking-details">
                        <!-- Details will be populated by JavaScript -->
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This action cannot be undone. You will receive a cancellation confirmation email.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">
                        <i class="fas fa-times me-1"></i> Yes, Cancel Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/my_bookings.js"></script>
</body>
</html> 