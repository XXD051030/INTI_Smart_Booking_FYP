-- Create facilities table for INTI Reservation System
CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('discussion_room', 'basketball_court', 'stem_lab') NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    location VARCHAR(200),
    image_path VARCHAR(255),
    advance_booking_days INT NOT NULL DEFAULT 0, -- 0 for discussion_room, 7 for basketball_court
    operating_start_time TIME DEFAULT '08:00:00',
    operating_end_time TIME DEFAULT '17:00:00',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active)
);

-- Insert sample facilities data
INSERT INTO facilities (name, type, description, capacity, location, image_path, advance_booking_days) VALUES
('Discussion Room A', 'discussion_room', 'Small meeting room for group discussions with whiteboard and basic AV equipment', 8, 'Library Level 2', 'images/place/discussion_room.jpg', 0),
('Discussion Room B', 'discussion_room', 'Medium meeting room with projector and comfortable seating for team meetings', 12, 'Library Level 3', 'images/place/discussion_room.jpg', 0),
('Discussion Room C', 'discussion_room', 'Large conference room with video conferencing capabilities', 16, 'Main Building Level 4', 'images/place/discussion_room.jpg', 0),
('Basketball Court', 'basketball_court', 'Full-size outdoor basketball court with proper lighting for evening sessions', 20, 'Sports Complex', 'images/place/basketball_court.jpg', 7),
('STEM Lab A', 'stem_lab', 'Modern laboratory with computer workstations and scientific equipment', 25, 'Engineering Building Level 2', 'images/place/stem_lab.jpg', 1); 