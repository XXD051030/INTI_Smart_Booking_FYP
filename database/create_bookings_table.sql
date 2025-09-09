-- Create bookings table for INTI Reservation System
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    facility_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    purpose VARCHAR(500) NOT NULL,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (facility_id, booking_date, start_time),
    INDEX idx_user_bookings (user_id, booking_date),
    INDEX idx_facility_date (facility_id, booking_date),
    INDEX idx_status (status),
    INDEX idx_user_daily_bookings (user_id, booking_date)
); 