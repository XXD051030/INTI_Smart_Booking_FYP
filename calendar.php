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
        // User not found in database, logout
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
    <title>Calendar - INTI Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <style>
        /* Modern Calendar Container */
        .calendar-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%, #f093fb 100%);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15), 0 10px 25px rgba(102, 126, 234, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .calendar-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.2), 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .calendar-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        #calendar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            z-index: 1;
            min-height: 600px;
            width: 100%;
        }

        /* Header Toolbar Styling */
        .fc-toolbar.fc-header-toolbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 25px;
            margin: 0;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: auto;
            border: none;
        }

        .fc-toolbar-title {
            color: #fff;
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 0.5px;
        }

        /* Navigation Buttons */
        .fc-button-group {
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 5px;
            backdrop-filter: blur(10px);
        }

        .fc-button {
            background: transparent !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600 !important;
            padding: 8px 15px !important;
            border-radius: 20px !important;
            transition: all 0.3s ease !important;
            margin: 0 2px !important;
        }

        .fc-button:hover {
            background: rgba(255,255,255,0.3) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }

        .fc-button:focus {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3) !important;
        }

        /* Day Headers */
        .fc-col-header {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8eeff 100%);
        }

        .fc-col-header-cell {
            background: transparent;
            height: 60px;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            border: none;
            position: relative;
        }

        .fc-col-header-cell::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
        }

        .fc-col-header-cell-cushion {
            color: #4a5568;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            line-height: 60px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Calendar Body */
        .fc-scrollgrid,
        .fc-scrollgrid-section-header,
        .fc-scrollgrid-section-body,
        .fc-scrollgrid table {
            margin: 0 !important;
            border: none !important;
            padding: 0 !important;
            border-collapse: collapse !important;
        }

        .fc-scrollgrid-section-header + .fc-scrollgrid-section-body {
            border-top: none !important;
        }

        /* Day Cells */
        .fc-daygrid-day {
            border: 1px solid rgba(0,0,0,0.05) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .fc-daygrid-day:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.02);
            z-index: 2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .fc-daygrid-day-number {
            color: #4a5568;
            font-weight: 600;
            font-size: 1rem;
            padding: 10px;
            text-decoration: none;
        }

        .fc-day-today {
            background: linear-gradient(135deg, rgba(246, 31, 31, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%) !important;
            border: 2px solid #f61f1f !important;
        }

        .fc-day-today .fc-daygrid-day-number {
            color: #f61f1f;
            font-weight: 700;
            background: rgba(246, 31, 31, 0.1);
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px auto;
        }

        /* Events Styling */
        .fc-event {
            background: linear-gradient(135deg, #f61f1f 0%, #ff6b6b 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 6px 12px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #fff !important;
            margin: 2px 4px !important;
            box-shadow: 0 4px 15px rgba(246, 31, 31, 0.3) !important;
            transition: all 0.3s ease !important;
            cursor: pointer !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .fc-event::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .fc-event:hover {
            transform: translateY(-3px) scale(1.05) !important;
            box-shadow: 0 8px 25px rgba(246, 31, 31, 0.4) !important;
            z-index: 10 !important;
        }

        .fc-event:hover::before {
            left: 100%;
        }

        .fc-event-title {
            font-weight: 600 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .fc-event-time {
            font-weight: 500 !important;
            opacity: 0.9;
        }

        /* Calendar Stats */
        .calendar-stats {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .stat-item {
            text-align: center;
            padding: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Enhanced Error Handling */
        .error-container {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 15px 35px rgba(255, 154, 158, 0.3);
        }
        
        .error-icon {
            font-size: 3rem;
            color: #e53e3e;
            margin-bottom: 15px;
        }
        
        .retry-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .retry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .calendar-container {
                padding: 15px;
                margin: 10px;
                border-radius: 16px;
            }
            
            .fc-toolbar-title {
                font-size: 1.4rem;
            }
            
            .fc-button {
                padding: 6px 10px !important;
                font-size: 0.8rem !important;
            }
            
            .calendar-stats {
                margin-bottom: 15px;
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .calendar-container {
                padding: 10px;
                margin: 5px;
            }
            
            .fc-toolbar.fc-header-toolbar {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }
            
            .fc-button-group {
                order: 1;
            }
            
            .fc-toolbar-title {
                order: 2;
                font-size: 1.2rem;
            }
            
            .stat-item {
                padding: 10px;
            }
        }

        /* Enhanced Loading Animation */
        .calendar-loading {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 300px;
            font-size: 1.2rem;
            color: #667eea;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(102, 126, 234, 0.2);
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loading-dots {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .loading-dot {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: bounce 1.4s ease-in-out infinite both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .loading-dot:nth-child(3) { animation-delay: 0s; }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            } 40% {
                transform: scale(1);
            }
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        .custom-toast {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Accessibility improvements */
        .fc-button:focus,
        .btn:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .calendar-container {
                border: 2px solid #000;
            }
            
            .fc-event {
                border: 1px solid #000 !important;
            }
        }
        
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="header">
            <div class="d-flex align-items-center">
                <img src="images/logo/inti_logo.png" alt="INTI Logo" height="40">
                <h2 class="ms-3 mb-0">Calendar View</h2>
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
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">
                        <i class="far fa-calendar me-2" style="color: #f61f1f;"></i>
                        <span style="color: #f61f1f; font-weight: 600;">Calendar Overview</span>
                    </h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" onclick="goToToday()">
                            <i class="fas fa-calendar-day me-1"></i> Today
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshCalendar()" title="Refresh Calendar">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="debugStats()" title="Debug Statistics">
                            <i class="fas fa-bug me-1"></i> Debug
                        </button>
                        <a href="booking.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> New Booking
                        </a>
                    </div>
                </div>

                <!-- Calendar Stats -->
                <div class="calendar-stats">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <span class="stat-number" id="total-bookings">-</span>
                                <span class="stat-label">Total Bookings</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <span class="stat-number" id="this-month">-</span>
                                <span class="stat-label">This Month</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <span class="stat-number" id="upcoming">-</span>
                                <span class="stat-label">Upcoming</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <span class="stat-number" id="this-week">-</span>
                                <span class="stat-label">This Week</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Container -->
                <div class="calendar-container">
                    <div id="calendar-loading" class="calendar-loading">
                        <div class="loading-spinner"></div>
                        <div>Loading calendar...</div>
                        <div class="loading-dots">
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                        </div>
                    </div>
                    <div id='calendar' style="display: none;"></div>
                    <div id="calendar-error" class="error-container" style="display: none;">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4>Calendar Loading Error</h4>
                        <p>We're having trouble loading your calendar. Please check your connection and try again.</p>
                        <button class="retry-btn" onclick="retryCalendarLoad()">
                            <i class="fas fa-sync-alt me-2"></i>Retry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        let calendar;
        let allEvents = [];

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const loadingEl = document.getElementById('calendar-loading');
            
            // Wait for layout to be ready
            setTimeout(() => {
                initializeCalendar(calendarEl, loadingEl);
            }, 50);
        });

        function initializeCalendar(calendarEl, loadingEl) {
            // Ensure container has proper dimensions before initializing
            const container = calendarEl.parentElement;
            if (container.offsetWidth === 0 || container.offsetHeight === 0) {
                console.log('Calendar container not ready, retrying...');
                setTimeout(() => initializeCalendar(calendarEl, loadingEl), 200);
                return;
            }

            console.log('Initializing calendar with container size:', container.offsetWidth, 'x', container.offsetHeight);
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,listWeek'
                },
                height: 'auto',
                aspectRatio: 1.35,
                firstDay: 1, // Monday
                weekends: true,
                dayMaxEvents: 3,
                moreLinkClick: 'popover',
                eventDisplay: 'block',
                displayEventTime: true,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    console.log('Fetching events from API...');
                    fetch('get_bookings.php')
                        .then(response => {
                            console.log('API response received:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Events data received:', data);
                            allEvents = data;
                            
                            // Update stats immediately after getting data
                            updateStats(data);
                            
                            // Pass data to calendar
                            successCallback(data);
                            
                            // Show calendar first, then hide loading
                            loadingEl.style.display = 'none';
                            calendarEl.style.display = 'block';
                            
                            // Force calendar re-render after showing to fix layout issues
                            setTimeout(() => {
                                if (calendar) {
                                    console.log('Re-rendering calendar after data load');
                                    calendar.updateSize();
                                    calendar.render();
                                    
                                    // Update stats again after render to ensure they're visible
                                    setTimeout(() => {
                                        updateStats(allEvents);
                                    }, 200);
                                }
                            }, 50);
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                            
                            // Reset stats on error
                            updateStats([]);
                            
                            // Show error container instead
                            showCalendarError();
                        });
                },
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                eventMouseEnter: function(info) {
                    info.el.style.transform = 'translateY(-3px) scale(1.05)';
                    info.el.style.zIndex = '100';
                },
                eventMouseLeave: function(info) {
                    info.el.style.transform = '';
                    info.el.style.zIndex = '';
                },
                dayCellDidMount: function(info) {
                    // Add subtle animation to date cells
                    info.el.addEventListener('mouseenter', function() {
                        this.style.transition = 'all 0.3s ease';
                    });
                },
                datesSet: function(dateInfo) {
                    // Update stats when view changes
                    console.log('Date view changed, updating stats with', allEvents.length, 'events');
                    if (allEvents && allEvents.length > 0) {
                        updateStats(allEvents);
                    }
                }
            });
            
            // Initial render
            calendar.render();

            // Use IntersectionObserver to ensure calendar is visible before final render
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && calendar) {
                        console.log('Calendar container is now visible, final render');
                        setTimeout(() => {
                            calendar.updateSize();
                            calendar.render();
                        }, 100);
                        observer.unobserve(calendarEl);
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(calendarEl);
        }

        function updateStats(events) {
            console.log('=== updateStats called ===');
            console.log('Events parameter:', events);
            console.log('Events length:', events ? events.length : 'undefined');
            console.log('allEvents variable:', allEvents);
            console.log('allEvents length:', allEvents ? allEvents.length : 'undefined');
            
            // Use allEvents if events parameter is empty/undefined
            const eventsToUse = (events && events.length > 0) ? events : allEvents;
            console.log('Using events:', eventsToUse);
            
            if (!eventsToUse || eventsToUse.length === 0) {
                console.log('No events found for statistics');
                animateNumber('total-bookings', 0);
                animateNumber('this-month', 0);
                animateNumber('upcoming', 0);
                animateNumber('this-week', 0);
                return;
            }

            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth();
            
            // Start of current month
            const startOfMonth = new Date(currentYear, currentMonth, 1);
            // End of current month
            const endOfMonth = new Date(currentYear, currentMonth + 1, 0, 23, 59, 59);
            
            // Start of current week (Monday)
            const startOfWeek = new Date(now);
            const dayOfWeek = now.getDay();
            const daysFromMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Sunday = 0, so 6 days from Monday
            startOfWeek.setDate(now.getDate() - daysFromMonday);
            startOfWeek.setHours(0, 0, 0, 0);
            
            // End of current week (Sunday)
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            endOfWeek.setHours(23, 59, 59, 999);

            console.log('Date ranges:');
            console.log('Now:', now.toISOString());
            console.log('Month:', startOfMonth.toISOString(), 'to', endOfMonth.toISOString());
            console.log('Week:', startOfWeek.toISOString(), 'to', endOfWeek.toISOString());

            const totalBookings = eventsToUse.length;
            
            const thisMonth = eventsToUse.filter(event => {
                const eventDate = new Date(event.start);
                const inMonth = eventDate >= startOfMonth && eventDate <= endOfMonth;
                console.log('Event:', event.start, 'in current month:', inMonth);
                return inMonth;
            }).length;
            
            const upcoming = eventsToUse.filter(event => {
                const eventDate = new Date(event.start);
                const isUpcoming = eventDate >= now;
                console.log('Event:', event.start, 'is upcoming:', isUpcoming);
                return isUpcoming;
            }).length;
            
            const thisWeek = eventsToUse.filter(event => {
                const eventDate = new Date(event.start);
                const inWeek = eventDate >= startOfWeek && eventDate <= endOfWeek;
                console.log('Event:', event.start, 'in current week:', inWeek);
                return inWeek;
            }).length;

            console.log('Statistics calculated:');
            console.log('Total:', totalBookings, 'This Month:', thisMonth, 'Upcoming:', upcoming, 'This Week:', thisWeek);

            // Animate numbers
            animateNumber('total-bookings', totalBookings);
            animateNumber('this-month', thisMonth);
            animateNumber('upcoming', upcoming);
            animateNumber('this-week', thisWeek);
        }

        function animateNumber(elementId, targetNumber) {
            const element = document.getElementById(elementId);
            const start = parseInt(element.textContent) || 0;
            const duration = 1000;
            const increment = (targetNumber - start) / (duration / 16);
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= targetNumber) || (increment < 0 && current <= targetNumber)) {
                    current = targetNumber;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        function showEventDetails(event) {
            const startTime = new Date(event.start).toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const endTime = new Date(event.end).toLocaleString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Determine event status
            const now = new Date();
            const eventStart = new Date(event.start);
            const eventEnd = new Date(event.end);
            let statusClass = 'info';
            let statusIcon = 'calendar-alt';
            let statusText = 'Scheduled';
            
            if (now < eventStart) {
                statusClass = 'primary';
                statusIcon = 'clock';
                statusText = 'Upcoming';
            } else if (now >= eventStart && now <= eventEnd) {
                statusClass = 'success';
                statusIcon = 'play-circle';
                statusText = 'Active Now';
            } else {
                statusClass = 'secondary';
                statusIcon = 'check-circle';
                statusText = 'Completed';
            }

            const alertHtml = `
                <div class="alert alert-${statusClass} alert-dismissible fade show event-details-alert" role="alert" style="
                    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
                    border: 1px solid rgba(102, 126, 234, 0.3);
                    border-radius: 15px;
                    backdrop-filter: blur(10px);
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                    transform: translateY(-10px);
                    animation: slideInDown 0.3s ease;
                ">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-${statusIcon}" style="color: #667eea; font-size: 1.8rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="alert-heading mb-0" style="color: #4a5568;">${event.title}</h5>
                                <span class="badge bg-${statusClass} rounded-pill">${statusText}</span>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><i class="fas fa-calendar me-2"></i><strong>Date:</strong> ${startTime}</p>
                                    <p class="mb-2"><i class="fas fa-clock me-2"></i><strong>End Time:</strong> ${endTime}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><i class="fas fa-hashtag me-2"></i><strong>Booking ID:</strong> #${event.id}</p>
                                    <a href="my_bookings.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            // Insert alert at the top of main content
            const mainContent = document.querySelector('.col-md-9.col-lg-10.p-4');
            const existingAlert = mainContent.querySelector('.event-details-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            mainContent.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-remove after 8 seconds
            setTimeout(() => {
                const alert = mainContent.querySelector('.event-details-alert');
                if (alert && alert.style.opacity !== '0') {
                    alert.style.transition = 'all 0.3s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 8000);
        }

        // Enhanced error handling
        function showCalendarError() {
            const loadingEl = document.getElementById('calendar-loading');
            const calendarEl = document.getElementById('calendar');
            const errorEl = document.getElementById('calendar-error');
            
            loadingEl.style.display = 'none';
            calendarEl.style.display = 'none';
            errorEl.style.display = 'block';
            
            // Add entrance animation
            errorEl.style.opacity = '0';
            errorEl.style.transform = 'translateY(20px)';
            setTimeout(() => {
                errorEl.style.transition = 'all 0.5s ease';
                errorEl.style.opacity = '1';
                errorEl.style.transform = 'translateY(0)';
            }, 50);
        }
        
        function hideCalendarError() {
            const errorEl = document.getElementById('calendar-error');
            errorEl.style.display = 'none';
        }
        
        function retryCalendarLoad() {
            const loadingEl = document.getElementById('calendar-loading');
            const calendarEl = document.getElementById('calendar');
            
            hideCalendarError();
            loadingEl.style.display = 'flex';
            calendarEl.style.display = 'none';
            
            // Reinitialize calendar
            setTimeout(() => {
                if (calendar) {
                    calendar.destroy();
                }
                initializeCalendar(calendarEl, loadingEl);
            }, 500);
        }

        // Add some global functions for the header buttons
        function goToToday() {
            if (calendar) {
                calendar.today();
                // Add visual feedback
                const todayBtn = event.target;
                todayBtn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    todayBtn.style.transform = '';
                }, 150);
            }
        }

        function refreshCalendar() {
            if (calendar) {
                console.log('Manual calendar refresh triggered');
                const loadingEl = document.getElementById('calendar-loading');
                const calendarEl = document.getElementById('calendar');
                const refreshBtn = document.querySelector('[onclick="refreshCalendar()"]');
                
                // Add visual feedback to refresh button
                if (refreshBtn) {
                    refreshBtn.disabled = true;
                    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
                }
                
                // Hide error if showing
                hideCalendarError();
                
                // Show loading briefly
                loadingEl.style.display = 'flex';
                calendarEl.style.display = 'none';
                
                setTimeout(() => {
                    // Force complete re-render
                    calendar.updateSize();
                    calendar.render();
                    calendar.refetchEvents(); // This will trigger the events callback and update stats
                    
                    // Hide loading and show calendar
                    loadingEl.style.display = 'none';
                    calendarEl.style.display = 'block';
                    
                    // Reset refresh button
                    if (refreshBtn) {
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh';
                    }
                    
                    // Also manually update stats after a short delay
                    setTimeout(() => {
                        if (allEvents && allEvents.length > 0) {
                            console.log('Manually updating stats after refresh');
                            updateStats(allEvents);
                        }
                    }, 500);
                    
                    console.log('Calendar manually refreshed');
                    
                    // Show success feedback
                    showToast('Calendar refreshed successfully!', 'success');
                }, 800);
            }
        }

        function debugStats() {
            console.log('=== DEBUG STATS TRIGGERED ===');
            console.log('allEvents:', allEvents);
            console.log('allEvents length:', allEvents ? allEvents.length : 'undefined');
            
            // Try to fetch fresh data
            fetch('get_bookings.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Fresh data from API:', data);
                    allEvents = data;
                    updateStats(data);
                    alert(`Debug completed! Check console. Stats: Total: ${data.length}`);
                })
                .catch(error => {
                    console.error('Debug fetch failed:', error);
                    alert('Debug failed - check console');
                });
        }

        // Add smooth scrolling and focus effects
        window.addEventListener('load', function() {
            // Add entrance animation to stats
            const statsElements = document.querySelectorAll('.stat-item');
            statsElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Ensure calendar is properly sized after full page load
            setTimeout(() => {
                if (calendar) {
                    console.log('Force calendar resize after page load');
                    calendar.updateSize();
                    calendar.render();
                    
                    // Force stats update if we have events
                    if (allEvents && allEvents.length > 0) {
                        console.log('Force stats update after page load with', allEvents.length, 'events');
                        updateStats(allEvents);
                    } else {
                        console.log('No allEvents available, trying to fetch directly...');
                        // Try to fetch events directly if allEvents is not available
                        fetch('get_bookings.php')
                            .then(response => response.json())
                            .then(data => {
                                console.log('Direct fetch for stats update:', data);
                                allEvents = data;
                                updateStats(data);
                            })
                            .catch(error => console.error('Direct fetch failed:', error));
                    }
                }
            }, 500);
        });

        // Handle window resize events
        window.addEventListener('resize', function() {
            if (calendar) {
                clearTimeout(window.resizeTimeout);
                window.resizeTimeout = setTimeout(() => {
                    console.log('Calendar resize on window resize');
                    calendar.updateSize();
                }, 250);
            }
        });

        // Add visibility change handler to fix rendering when tab becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && calendar) {
                setTimeout(() => {
                    console.log('Calendar resize on visibility change');
                    calendar.updateSize();
                }, 100);
            }
        });
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 3000) {
            const toastContainer = getOrCreateToastContainer();
            const toastId = 'toast-' + Date.now();
            
            const iconMap = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            
            const toastHtml = `
                <div id="${toastId}" class="toast custom-toast" role="alert">
                    <div class="toast-header border-0">
                        <i class="fas fa-${iconMap[type]} text-${type} me-2"></i>
                        <strong class="me-auto">Calendar</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: duration
            });
            
            toast.show();
            
            // Clean up after hiding
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
        
        function getOrCreateToastContainer() {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            return container;
        }
        
        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + R to refresh calendar
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                refreshCalendar();
            }
            
            // T key to go to today
            if (e.key === 't' || e.key === 'T') {
                if (!e.target.matches('input, textarea, [contenteditable]')) {
                    e.preventDefault();
                    goToToday();
                }
            }
            
            // Escape to close any open alerts
            if (e.key === 'Escape') {
                const alerts = document.querySelectorAll('.alert .btn-close');
                alerts.forEach(btn => btn.click());
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
