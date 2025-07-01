<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Please login first']);
    exit;
}

// Include database connection
require_once '../db.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Handle export request
    if (isset($_GET['export']) && $_GET['export'] == '1') {
        exportBookings($pdo);
        exit;
    }

    // Handle single booking detail request
    if (isset($_GET['booking_id'])) {
        getSingleBooking($pdo, $_GET['booking_id']);
        exit;
    }

    // Get parameters
    $date = $_GET['date'] ?? date('Y-m-d');
    $status = $_GET['status'] ?? '';
    $facility = $_GET['facility'] ?? '';

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        exit;
    }

    // Build query conditions (facility filter will be handled in frontend)
    $conditions = ["booking_date = ?"];
    $params = [$date];

    if (!empty($status)) {
        $conditions[] = "b.status = ?";
        $params[] = $status;
    }

    // Note: facility filter is handled in frontend to maintain full table view
    $whereClause = "WHERE " . implode(" AND ", $conditions);

    // Get bookings with user and facility information
    $bookings_query = "
        SELECT 
            b.booking_id,
            b.user_id,
            b.facility_id,
            b.booking_date,
            b.start_time,
            b.end_time,
            b.purpose,
            b.status,
            b.created_at,
            b.cancelled_at,
            u.username,
            u.email,
            f.name as facility_name,
            f.location,
            f.capacity
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN facilities f ON b.facility_id = f.facility_id
        $whereClause
        ORDER BY b.start_time, f.name
    ";

    $stmt = $pdo->prepare($bookings_query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    // Calculate statistics
    $stats_query = "
        SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
            SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
        FROM bookings b
        $whereClause
    ";

    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch();

    // Calculate utilization rate
    // Get total available slots for the date
    $facilities_stmt = $pdo->prepare("SELECT COUNT(*) as facility_count FROM facilities WHERE is_active = 1");
    $facilities_stmt->execute();
    $facility_count = $facilities_stmt->fetch()['facility_count'];

    $time_slots_count = 9; // 08:00 to 16:00 (9 slots)
    $total_slots = $facility_count * $time_slots_count;
    $occupied_slots = $stats['confirmed_bookings'];
    $utilization_rate = $total_slots > 0 ? round(($occupied_slots / $total_slots) * 100, 1) : 0;

    $statistics = [
        'total' => (int)$stats['total_bookings'],
        'confirmed' => (int)$stats['confirmed_bookings'],
        'cancelled' => (int)$stats['cancelled_bookings'],
        'utilization_rate' => $utilization_rate
    ];

    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'statistics' => $statistics,
        'date' => $date
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_bookings.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in get_bookings.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching bookings']);
}

function getSingleBooking($pdo, $booking_id) {
    try {
        if (!is_numeric($booking_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
            return;
        }

        $query = "
            SELECT 
                b.booking_id,
                b.user_id,
                b.facility_id,
                b.booking_date,
                b.start_time,
                b.end_time,
                b.purpose,
                b.status,
                b.created_at,
                b.cancelled_at,
                u.username,
                u.email,
                f.name as facility_name,
                f.location,
                f.capacity,
                f.type as facility_type
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN facilities f ON b.facility_id = f.facility_id
            WHERE b.booking_id = ?
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();

        if ($booking) {
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }

    } catch (PDOException $e) {
        error_log("Database error in getSingleBooking: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function exportBookings($pdo) {
    try {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo "Invalid date format";
            return;
        }

        $query = "
            SELECT 
                b.booking_id as 'Booking ID',
                f.name as 'Facility',
                f.location as 'Location',
                u.username as 'Username',
                u.email as 'Email',
                b.booking_date as 'Date',
                b.start_time as 'Start Time',
                b.end_time as 'End Time',
                b.purpose as 'Purpose',
                b.status as 'Status',
                b.created_at as 'Created At',
                b.cancelled_at as 'Cancelled At'
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN facilities f ON b.facility_id = f.facility_id
            WHERE b.booking_date = ?
            ORDER BY b.start_time, f.name
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$date]);
        $bookings = $stmt->fetchAll();

        // Set headers for CSV download
        $filename = "bookings_" . $date . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create CSV output
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for proper Excel encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        if (!empty($bookings)) {
            // Output headers
            fputcsv($output, array_keys($bookings[0]));
            
            // Output data
            foreach ($bookings as $booking) {
                fputcsv($output, $booking);
            }
        } else {
            // Output headers only if no data
            fputcsv($output, [
                'Booking ID', 'Facility', 'Location', 'Username', 'Email',
                'Date', 'Start Time', 'End Time', 'Purpose', 'Status',
                'Created At', 'Cancelled At'
            ]);
            fputcsv($output, ['No bookings found for ' . $date]);
        }

        fclose($output);
        
    } catch (PDOException $e) {
        error_log("Database error in exportBookings: " . $e->getMessage());
        echo "Error exporting bookings: Database error";
    } catch (Exception $e) {
        error_log("General error in exportBookings: " . $e->getMessage());
        echo "Error exporting bookings: " . $e->getMessage();
    }
}
?> 