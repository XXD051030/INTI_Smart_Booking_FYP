-- Create facilities table for INTI Reservation System
CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('discussion_room', 'basketball_court', 'sports_field', 'tennis_court') NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    location VARCHAR(200),
    image_path VARCHAR(255),
    advance_booking_days INT NOT NULL DEFAULT 0, -- 0 for discussion_room, 7 for sports facilities
    operating_start_time TIME DEFAULT '08:00:00',
    operating_end_time TIME DEFAULT '18:00:00',
    operating_days VARCHAR(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri', -- Which days the facility operates
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active),
    INDEX idx_operating_days (operating_days)
);

-- Insert updated facilities data with correct schedules and days
INSERT INTO facilities (name, type, description, capacity, location, image_path, advance_booking_days, operating_start_time, operating_end_time, operating_days) VALUES
-- Discussion Rooms (Mon - Fri, 8am - 6pm)
('Discussion Room 1', 'discussion_room', 'Standard meeting room for group discussions (3-4 people)', 4, 'Library Level 6', 'images/place/discussion_room.jpg', 0, '08:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri'),
('Discussion Room 2', 'discussion_room', 'Standard meeting room for group discussions (3-4 people)', 4, 'Library Level 6', 'images/place/discussion_room.jpg', 0, '08:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri'),
('Discussion Room 3', 'discussion_room', 'Standard meeting room for group discussions (3-4 people)', 4, 'Library Level 6', 'images/place/discussion_room.jpg', 0, '08:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri'),
('Discussion Room 4', 'discussion_room', 'Medium meeting room for team discussions (3-6 people)', 6, 'Library Level 6', 'images/place/discussion_room.jpg', 0, '08:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri'),
('Discussion Room 5', 'discussion_room', 'Large meeting room for group discussions (6-10 people)', 10, 'Library Level 6', 'images/place/discussion_room.jpg', 0, '08:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri'),

-- Basketball Court (Mon - Sat, 7am - 7pm)
('Basketball Court', 'basketball_court', 'Full-size outdoor basketball court with proper lighting', 20, 'Sports Complex', 'images/place/basketball_court.jpg', 7, '07:00:00', '19:00:00', 'Mon,Tue,Wed,Thu,Fri,Sat'),

-- Sports Field (Wed, Thu, & Sat, 7am - 7pm)  
('Sports Field', 'sports_field', 'Multi-purpose outdoor sports field for various activities', 30, 'Sports Complex', 'images/place/sports_field.jpg', 3, '07:00:00', '19:00:00', 'Wed,Thu,Sat'),

-- Tennis Court (Mon - Sat, 7am - 7pm)
('Tennis Court', 'tennis_court', 'Professional tennis court with proper surface and lighting', 4, 'Sports Complex', 'images/place/tennis_court.jpg', 3, '07:00:00', '19:00:00', 'Mon,Tue,Wed,Thu,Fri,Sat');