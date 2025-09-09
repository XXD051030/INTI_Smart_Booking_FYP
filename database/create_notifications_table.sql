-- Create notifications table for INTI Reservation System
USE reservation_system;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('booking_confirmed', 'booking_cancelled', 'booking_reminder', 'system_notice') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_booking_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_user_unread (user_id, is_read)
);

-- Sample notification will be inserted after user registration
-- INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
-- (1, 'system_notice', 'Welcome to INTI Reservation System', 'Welcome to the new notification system! You will receive updates about your bookings and important announcements here.', FALSE); 