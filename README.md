# 🎓 INTI Student Registration & Facility Booking System

A comprehensive student registration, authentication, and facility booking system with email verification functionality, specifically designed for INTI University students.

## 📋 **System Overview**

### 🎯 **Key Features**
- ✅ Student Registration (INTI email only)
- ✅ Email OTP Verification
- ✅ User Login/Logout
- ✅ Personal Dashboard
- ✅ **Multi-Slot Facility Booking System** 🆕
- ✅ **1-2 Hour Consecutive Bookings** 🆕
- ✅ **Real-time Availability Checking** 🆕
- ✅ **Booking Management & Cancellation** 🆕
- ✅ PHPMailer Email System
- ✅ Responsive Design
- ✅ Real-time Form Validation
- ✅ Modern UI/UX Design

### 🛠️ **Technology Stack**
- **Backend**: PHP 8.x, MySQL 8.x
- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5
- **Email**: PHPMailer
- **Database**: MySQL with PDO
- **Dependency Management**: Composer
- **Authentication**: Session-based Authentication

---

## 📁 **Project Structure**

```
/var/www/html/
├── 📱 **Core Pages**
│   ├── register.php              # Student registration page
│   ├── process_register.php      # Registration data processing
│   ├── login.php                 # Login page
│   ├── login_handler.php         # Login processing
│   ├── general.php               # User dashboard
│   ├── otp-verify.php           # OTP verification page
│   └── logout.php               # Logout handler
│
├── 🏢 **Booking System** 🆕
│   ├── booking.php              # Multi-slot facility booking interface
│   ├── process_booking.php      # Booking data processing & validation
│   ├── check_availability.php   # Real-time availability API
│   ├── my_bookings.php         # Booking management & history
│   └── cancel_booking.php      # Booking cancellation handler
│
├── ⚙️ **Configuration Files**
│   ├── db.php                   # Database connection config
│   ├── mail_config.php          # Email server configuration
│   ├── function.php             # Common functions library
│   └── password-validation.php  # Password validation functions
│
├── 📧 **Email System**
│   ├── Mailer.php              # PHPMailer email class
│   ├── composer.json           # Composer dependencies
│   ├── composer.lock           # Dependency lock file
│   └── vendor/                 # Composer packages
│
├── 🗄️ **Database Scripts**
│   ├── create_users_table.sql   # Users table structure
│   ├── create_otp_table.sql     # OTP table structure
│   ├── create_facilities_table.sql # Facilities table structure 🆕
│   ├── create_bookings_table.sql   # Bookings table structure 🆕
│   └── check.php               # System health check
│
├── 🎨 **Frontend Assets**
│   ├── css/
│   │   ├── style.css           # Main stylesheet
│   │   ├── login.css           # Login page styles
│   │   ├── booking.css         # Booking system styles 🆕
│   │   └── otp-verify.css      # OTP verification styles
│   ├── js/
│   │   ├── validations.js      # Form validation scripts
│   │   ├── booking.js          # Multi-slot booking logic 🆕
│   │   ├── my_bookings.js      # Booking management scripts 🆕
│   │   └── countdown.js        # OTP countdown timer
│   └── images/
│       ├── logo/               # Logo images
│       ├── place/              # Facility images
│       └── assets/             # Other resources
│
├── 📋 **Legal & Documentation**
│   ├── rules.php              # Terms and conditions
│   ├── README.md              # Project documentation
│   ├── BOOKING_SYSTEM_README.md # Detailed booking system docs 🆕
│   └── admin/                 # Admin panel (future)
```

---

## 🗄️ **Database Structure**

### Database Information
- **Database Name**: `reservation_system`
- **Username**: `webapp`
- **Password**: `webapp123`
- **Host**: `localhost`
- **Port**: `3306`

### Table Structure

#### 📋 **users Table**
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_verified (is_verified),
    INDEX idx_username (username)
);
```

#### 🔢 **user_otp Table**
```sql
CREATE TABLE user_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_otp_code (otp_code)
);
```

#### 🏢 **facilities Table** 🆕
```sql
CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('discussion_room', 'basketball_court', 'stem_lab') NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(150) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    advance_booking_days INT DEFAULT 7,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active)
);
```

#### 📅 **bookings Table** 🆕
```sql
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    facility_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    purpose TEXT NOT NULL,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, booking_date),
    INDEX idx_facility_date (facility_id, booking_date),
    INDEX idx_status (status),
    UNIQUE KEY unique_booking (facility_id, booking_date, start_time)
);
```

---

## 🚀 **Installation Guide**

### 1. **System Requirements**
```bash
- PHP >= 8.0
- MySQL >= 8.0
- Apache/Nginx web server
- Composer (for dependency management)
- Required PHP extensions: pdo_mysql, curl, openssl, mbstring
- SSL certificate (recommended for production)
```

### 2. **Database Setup**
```bash
# Connect to MySQL as root
sudo mysql -u root -p

# Create database
CREATE DATABASE reservation_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create database user
CREATE USER 'webapp'@'localhost' IDENTIFIED BY 'webapp123';
GRANT ALL PRIVILEGES ON reservation_system.* TO 'webapp'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import table structures
mysql -u webapp -p reservation_system < create_users_table.sql
mysql -u webapp -p reservation_system < create_otp_table.sql
mysql -u webapp -p reservation_system < create_facilities_table.sql
mysql -u webapp -p reservation_system < create_bookings_table.sql
```

### 3. **Install Dependencies**
```bash
# Install Composer (if not already installed)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Navigate to project directory
cd /var/www/html

# Install PHPMailer and other dependencies
composer install --no-dev --optimize-autoloader
```

### 4. **Email Configuration**
Edit `mail_config.php` to configure SMTP settings:
```php
<?php
// Gmail SMTP example
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password for Gmail
define('SMTP_ENCRYPTION', 'tls');
define('FROM_EMAIL', 'noreply@inti.edu.my');
define('FROM_NAME', 'INTI Reservation System');
?>
```

### 5. **File Permissions**
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html

# Set correct permissions
sudo chmod -R 755 /var/www/html
sudo chmod -R 644 /var/www/html/*.php

# Create and set log directory permissions
sudo mkdir -p /var/www/html/var/log
sudo chmod 777 /var/www/html/var/log
```

### 6. **Web Server Configuration**

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/inti_system_error.log
    CustomLog ${APACHE_LOG_DIR}/inti_system_access.log combined
</VirtualHost>
```

---

## 🏢 **Multi-Slot Booking System** 🆕

### 🎯 **Booking Features**
- **Multi-Time Slot Selection**: Book 1 or 2 consecutive hours
- **Real-Time Availability**: Live checking with visual feedback  
- **Smart Validation**: Ensures consecutive slot booking only
- **Daily Limits**: Maximum 2 bookings per user per day
- **Advanced Booking Rules**: 
  - Discussion Rooms: Same day only
  - Basketball Court: Up to 7 days advance
  - STEM Lab: Up to 1 day advance
- **Instant Cancellation**: Cancel up to 30 minutes before start time
- **Email Notifications**: Confirmation and cancellation emails

### 🏗️ **Available Facilities**
| Facility | Type | Capacity | Advance Booking | Operating Hours |
|----------|------|----------|----------------|-----------------|
| Discussion Room A | Discussion Room | 8 people | Same day only | 08:00 - 17:00 |
| Discussion Room B | Discussion Room | 10 people | Same day only | 08:00 - 17:00 |
| Discussion Room C | Discussion Room | 6 people | Same day only | 08:00 - 17:00 |
| Basketball Court | Sports Court | 20 people | Up to 7 days | 08:00 - 17:00 |
| STEM Lab | Laboratory | 15 people | Up to 1 day | 08:00 - 17:00 |

### 🎮 **How Multi-Slot Booking Works**

#### 1. **Single Hour Booking** (Traditional)
```
User selects: 09:00
Result: 09:00 - 10:00 (1 hour)
```

#### 2. **Double Hour Booking** (New Feature) 🆕
```
User selects: 09:00, then 10:00
Result: 09:00 - 11:00 (2 consecutive hours)
```

#### 3. **Smart Validation**
- ✅ **Allowed**: 09:00 + 10:00 (consecutive)
- ❌ **Blocked**: 09:00 + 11:00 (not consecutive)
- ❌ **Blocked**: More than 2 slots selected
- ❌ **Blocked**: Slots on different days

### 📱 **Booking Interface Guide**

#### **Step 1: Select Facility** (`/booking.php`)
- Filter by type (Discussion/Sports/STEM)
- View capacity and advance booking rules
- Real-time facility availability status

#### **Step 2: Choose Date**
- Date picker with validation
- Shows maximum advance booking allowed
- Disabled dates for same-day-only facilities

#### **Step 3: Multi-Slot Time Selection** 🆕
- **Visual Time Grid**: 9 hourly slots (08:00-16:00)
- **Color-Coded Status**:
  - 🟢 **Green**: Available slots
  - 🔵 **Blue**: Your selected slots
  - 🔴 **Red**: Already booked
  - ⚪ **Gray**: Non-consecutive (when 1+ slot selected)
- **Interactive Selection**:
  - Click to select/deselect slots
  - Maximum 2 consecutive slots
  - Real-time validation feedback

#### **Step 4: Booking Confirmation**
- **Dynamic Summary**: Shows total duration (1-2 hours)
- **Purpose Description**: Minimum 10 characters required
- **Daily Limit Check**: Prevents exceeding 2 bookings/day
- **Instant Processing**: Real-time availability recheck

### 🔧 **Booking Management** (`/my_bookings.php`)

#### **Booking History Features**
- **Advanced Filtering**: By status, date range, facility type
- **Pagination**: Handle large booking histories
- **Booking Details**: Complete information display
- **Status Tracking**: Confirmed, Cancelled with timestamps

#### **Smart Cancellation**
- **Time Window**: Cancel up to 30 minutes before start
- **Automatic Validation**: Prevents late cancellations
- **Email Confirmation**: Cancellation notification sent
- **Multi-Slot Handling**: Cancels all related consecutive bookings

### 📊 **Booking Business Logic**

#### **Daily Booking Limits**
- **Rule**: Maximum 2 bookings per user per day
- **Counting**: Each time slot = 1 booking
- **Example**: 
  - ✅ 09:00-10:00 + 14:00-15:00 = 2 bookings (allowed)
  - ❌ 09:00-10:00 + 11:00-12:00 + 14:00-15:00 = 3 bookings (blocked)

#### **Advance Booking Rules**
```php
// Discussion Rooms: Same day only
if ($facility_type === 'discussion_room' && $booking_date > $today) {
    return 'error: Same day booking only';
}

// Basketball Court: Up to 7 days
if ($facility_type === 'basketball_court' && $days_advance > 7) {
    return 'error: Maximum 7 days advance booking';
}

// STEM Lab: Up to 1 day  
if ($facility_type === 'stem_lab' && $days_advance > 1) {
    return 'error: Maximum 1 day advance booking';
}
```

#### **Consecutive Slot Validation**
```javascript
// JavaScript validation for consecutive slots
function isConsecutiveSlot(newSlot) {
    if (selectedSlots.length === 0) return true;
    
    const timeIndex = TIME_SLOTS.indexOf(newSlot);
    const selectedIndices = selectedSlots.map(slot => TIME_SLOTS.indexOf(slot));
    
    // Check if adjacent to any selected slot
    return selectedIndices.some(index => Math.abs(timeIndex - index) === 1);
}
```

---

## 📖 **User Guide**

### 🎯 **Registration Process**

#### 1. **Student Registration** (`/register.php`)
- **Access**: Navigate to `http://your-domain/register.php`
- **Requirements**:
  - Valid INTI student email (`@student.newinti.edu.my`)
  - Unique username (3-50 characters)
  - Strong password (minimum 6 characters, at least 1 number)
- **Features**:
  - Real-time email format validation
  - Password strength indicator
  - Terms and conditions acceptance
  - Responsive design for all devices

#### 2. **Email Verification** (`/otp-verify.php`)
- **Process**:
  1. Click "Send OTP" to receive verification code
  2. Check your INTI email for 6-digit code
  3. Enter the code within 15 minutes
  4. Account activation upon successful verification
- **Features**:
  - Countdown timer display
  - Resend OTP functionality
  - Auto-redirect after verification

#### 3. **User Login** (`/login.php`)
- **Credentials**: Use registered email and password
- **Features**:
  - "Remember me" functionality
  - Password visibility toggle
  - Responsive error handling
  - Automatic redirect to dashboard

#### 4. **User Dashboard** (`/general.php`)
- **Access**: Available after successful login
- **Features**:
  - Personal profile information
  - Quick access to booking system
  - Recent booking history
  - Account settings
  - Secure logout option

#### 5. **Facility Booking** (`/booking.php`) 🆕
- **Multi-Slot Selection**: Choose 1 or 2 consecutive time slots
- **Smart Interface**: 
  - Real-time availability checking
  - Visual feedback for slot selection
  - Consecutive slot validation
- **Booking Process**:
  1. Select facility from categorized list
  2. Choose valid date within advance booking limit
  3. Select 1-2 consecutive time slots
  4. Enter booking purpose (min 10 characters)
  5. Confirm booking with email notification

#### 6. **Booking Management** (`/my_bookings.php`) 🆕
- **Comprehensive History**: View all past and upcoming bookings
- **Advanced Filtering**: 
  - Filter by status (All/Confirmed/Cancelled)
  - Date range selection
  - Facility type filtering
- **Booking Actions**:
  - View detailed booking information
  - Cancel bookings (up to 30 minutes before start)
  - Download booking confirmations
- **Pagination**: Handle large booking histories efficiently

---

## 🛡️ **Security Features**

### 🔐 **Password Security**
- **Encryption**: BCrypt hashing algorithm
- **Validation**: Minimum 6 characters with numeric requirement
- **Storage**: Never stored in plain text
- **Session**: Secure session management with timeout

### 📧 **Email Verification**
- **OTP Validity**: 15-minute expiration time
- **Format Validation**: Strict INTI email domain checking
- **Rate Limiting**: Prevents spam and abuse
- **Single Use**: OTP codes are invalidated after use

### 🚫 **Security Measures**
- **SQL Injection**: PDO prepared statements
- **XSS Protection**: Input sanitization with `htmlspecialchars()`
- **CSRF Protection**: Session-based validation
- **Data Validation**: Server-side and client-side validation
- **Session Security**: Secure session configuration

---

## 🔧 **API Endpoints**

### Authentication Endpoints
```
POST /login_handler.php
POST /process_register.php
POST /logout.php
```

### OTP Endpoints
```
POST /otp-verify.php (send OTP)
POST /otp-verify.php (verify OTP)
```

### Booking System APIs 🆕
```
POST /check_availability.php        # Real-time slot availability
POST /process_booking.php          # Multi-slot booking creation
POST /cancel_booking.php           # Booking cancellation
GET  /my_bookings.php              # User booking history (with AJAX)
```

#### **API Details**

##### `/check_availability.php`
```json
// Request
{
    "facility_id": "1",
    "date": "2024-06-24",
    "action": "check_availability"
}

// Response
{
    "success": true,
    "available_slots": [
        {"time": "08:00", "available": true},
        {"time": "09:00", "available": false},
        {"time": "10:00", "available": true}
    ]
}
```

##### `/process_booking.php`
```json
// Request (Multi-slot)
{
    "facility_id": "1",
    "booking_date": "2024-06-24",
    "time_slots": "[\"09:00\",\"10:00\"]",
    "start_time": "09:00",
    "end_time": "11:00", 
    "slot_count": 2,
    "purpose": "Team meeting and presentation"
}

// Response
{
    "success": true,
    "booking_ids": [123, 124],
    "primary_booking_id": 123,
    "slot_count": 2,
    "booking_time": "9:00 AM - 11:00 AM (2 hours, consecutive slots)"
}
```

---

## ⚠️ **Known Issues & Solutions**

### 🔧 **Common Issues**

1. **Email Delivery Problems**
   ```bash
   # Check SMTP configuration
   # Verify Gmail App Password
   # Check spam/junk folders
   ```

2. **Database Connection Issues**
   ```bash
   # Verify MySQL service status
   sudo systemctl status mysql
   
   # Check database credentials in db.php
   # Ensure user has proper permissions
   ```

3. **File Permission Errors**
   ```bash
   # Reset permissions
   sudo chown -R www-data:www-data /var/www/html
   sudo chmod -R 755 /var/www/html
   ```

4. **Missing Dependencies**
   ```bash
   # Reinstall Composer packages
   composer install
   
   # Check PHP extensions
   php -m | grep -E "(pdo_mysql|curl|openssl)"
   ```

---

## 📊 **System Health Check**

### ✅ **Working Components**
- [x] Student registration form (HTTP 200)
- [x] Login system (HTTP 200)
- [x] Email verification logic
- [x] OTP generation and sending
- [x] Database connectivity
- [x] User authentication system
- [x] **Multi-Slot Booking System** 🆕
- [x] **Real-time Availability Checking** 🆕
- [x] **Consecutive Slot Validation** 🆕
- [x] **Booking Management Interface** 🆕
- [x] **Smart Cancellation System** 🆕
- [x] PHPMailer integration
- [x] Responsive design
- [x] Form validations
- [x] **Malaysia Timezone Configuration (UTC+8)** 🆕

### 🔄 **Redirect Pages** (Normal Behavior)
- [x] User dashboard (HTTP 302 - requires login)
- [x] OTP verification (HTTP 302 - requires session)
- [x] Admin panel (HTTP 302 - requires admin privileges)

---

## 🧪 **Testing Guide**

### 📝 **Manual Testing Checklist**

#### Registration Flow Test
   ```
1. Navigate to /register.php
2. Enter test data:
   - Email: test@student.newinti.edu.my
   - Username: testuser123
   - Password: TestPass123
3. Verify email validation works
4. Submit form and check redirection
5. Verify database entry created
```

#### Email Verification Test
```
1. Click "Send OTP" button
2. Check email inbox for OTP code
3. Enter correct/incorrect codes
4. Verify countdown timer
5. Test OTP expiration
```

#### Login System Test
```
1. Use verified account credentials
2. Test "Remember me" functionality
3. Verify session persistence
4. Test logout functionality
```

#### Multi-Slot Booking System Test 🆕
```
1. **Facility Selection Test**
   - Navigate to /booking.php
   - Test facility type filters (All/Discussion/Sports/STEM)
   - Verify facility information display
   - Check advance booking rules

2. **Date Selection Test**
   - Test date picker functionality
   - Verify advance booking validation
   - Check same-day-only restrictions for discussion rooms

3. **Multi-Slot Time Selection Test**
   - Select single time slot (traditional booking)
   - Select 2 consecutive slots (new feature)
   - Try selecting non-consecutive slots (should be blocked)
   - Try selecting more than 2 slots (should be blocked)
   - Verify visual feedback (colors, disabled states)

4. **Booking Validation Test**
   - Test purpose field validation (min 10 chars)
   - Check daily booking limit (max 2 per day)
   - Verify slot conflict detection
   - Test booking confirmation email

5. **Booking Management Test**
   - Navigate to /my_bookings.php
   - Test filtering by status/date/facility type
   - Test booking cancellation (within 30-min window)
   - Verify cancellation email notification
   - Test pagination with multiple bookings
   ```

### 🔧 **Automated Testing Commands**
```bash
# Test database connection
php -r "include 'db.php'; echo 'Database: Connected successfully\n';"

# Test page accessibility
curl -I http://localhost/register.php
curl -I http://localhost/login.php
curl -I http://localhost/booking.php          # Booking system
curl -I http://localhost/my_bookings.php      # Booking management

# Test booking APIs
curl -X POST http://localhost/check_availability.php \
  -d "facility_id=1&date=2024-06-24" \
  -H "Content-Type: application/x-www-form-urlencoded"

# Check Composer dependencies
composer validate
composer install --dry-run

# Test email configuration
php test_mail.php

# Verify booking system database tables
mysql -u webapp -p reservation_system -e "
  SHOW TABLES LIKE '%facilities%';
  SHOW TABLES LIKE '%bookings%';
  SELECT COUNT(*) as facility_count FROM facilities;
"

# Test timezone configuration
php -r "
  echo 'PHP Timezone: ' . date_default_timezone_get() . '\n';
  echo 'Current Time: ' . date('Y-m-d H:i:s T') . '\n';
"
```

---

## 📈 **Future Development Roadmap**

### 🚀 **Phase 1: Core Features** (✅ Completed)
- [x] User registration and authentication
- [x] Email verification system
- [x] Responsive UI design
- [x] Basic security measures

### 🏢 **Phase 2: Multi-Slot Booking System** (✅ Completed) 🆕
- [x] **Multi-slot facility booking (1-2 consecutive hours)**
- [x] **Real-time availability checking with visual feedback**
- [x] **Smart consecutive slot validation**
- [x] **Advanced booking rules (same-day, 7-day, 1-day advance)**
- [x] **Comprehensive booking management interface**
- [x] **Smart cancellation system (30-minute window)**
- [x] **Email notifications for bookings & cancellations**
- [x] **Daily booking limits (max 2 per user)**
- [x] **Timezone synchronization (Malaysia UTC+8)**

### 🔧 **Phase 3: Admin & Advanced Features** (✅ Partially Completed)
- [x] **Admin dashboard for facility management** 🆕
- [ ] Booking analytics and reporting system
- [ ] Push notification system
- [ ] Mobile app development
- [ ] QR code check-in system

### 🛡️ **Phase 4: Enterprise Features** (Future)
- [ ] Multi-language support (English/Bahasa Malaysia/Chinese)
- [ ] Single Sign-On (SSO) integration
- [ ] LDAP/Active Directory integration
- [ ] Advanced analytics dashboard
- [ ] Automated backup and recovery system
- [ ] Integration with INTI student information system

---

## 📞 **Support & Maintenance**

### 🐛 **Issue Reporting**
When reporting issues, please include:
1. Error message (if any)
2. Steps to reproduce
3. Browser and version
4. System environment details

### 📋 **Log Files Location**
```bash
# Application logs
/var/www/html/var/log/

# Apache logs
/var/log/apache2/

# MySQL logs
/var/log/mysql/

# PHP logs
/var/log/php/
```

### 🔧 **Troubleshooting FAQ**

**Q: Email sending fails with "Authentication failed"**
```
A: Check SMTP credentials in mail_config.php
   For Gmail: Use App Password instead of regular password
   Enable 2-Factor Authentication first
```

**Q: "Database connection failed" error**
```
A: 1. Verify MySQL service is running
   2. Check database credentials in db.php
   3. Ensure database user has proper permissions
   4. Test connection with: php -r "include 'db.php';"
```

**Q: OTP emails not received**
```
A: 1. Check spam/junk folders
   2. Verify SMTP configuration
   3. Test with test_mail.php script
   4. Check email server logs
```

**Q: Page shows white screen (500 error)**
```
A: 1. Check Apache error logs
   2. Verify PHP error reporting is enabled
   3. Check file permissions
   4. Ensure all dependencies are installed
```

---

## 📋 **Version History & Updates**

### 🆕 **V0.3.1 - Enhanced Admin Dashboard** (January 2025)
**Admin Experience & UI Improvements**

#### ✨ **New Features**
- **Enhanced Admin Booking View**: Improved facility filtering system
- **Smart Visual Filtering**: Highlight selected facilities while keeping full table view
- **Optimized Display**: Removed purpose column from time table to reduce clutter
- **Better User Experience**: Maintain table layout integrity during filtering

#### 🛠️ **Admin Dashboard Improvements**
- **Facility Filter Enhancement**: 
  - Visual highlighting of selected facilities
  - Semi-transparent display of non-selected facilities
  - Complete table structure preserved during filtering
- **UI Optimization**:
  - Cleaner time table display without overwhelming text
  - Better visual hierarchy in booking information
  - Improved responsiveness and readability

#### 🛠️ **Technical Improvements V0.3.1**
- **Admin Interface Optimization**: Enhanced facility filtering without data loss
- **Frontend Performance**: 
  - Client-side filtering for better user experience
  - Visual highlighting system for facility selection
  - Optimized CSS for better readability
- **Backend Efficiency**: 
  - Reduced server requests by handling facility filters in frontend
  - Maintained full data integrity during filtering operations

#### 📁 **Files Updated**
```
🔧 Admin Dashboard Enhancements:
├── admin/bookings.php           # Enhanced facility filtering logic
├── admin/get_bookings.php       # Optimized data filtering
├── admin/css/bookings.css       # New visual filtering styles
└── README.md                    # Updated documentation
```

### **V0.3.0 - Multi-Slot Booking System** (2024)
**Major Feature Release: Advanced Booking System**

#### ✨ **New Features**
- **Multi-Slot Booking**: Users can now book 1 or 2 consecutive time slots
- **Real-Time Availability**: Live checking with instant visual feedback
- **Smart Validation**: Automatic consecutive slot validation
- **Advanced Business Rules**: 
  - Discussion Rooms: Same-day booking only
  - Basketball Court: Up to 7 days advance booking
  - STEM Lab: Up to 1 day advance booking
- **Comprehensive Booking Management**: Full booking history with filtering
- **Smart Cancellation**: 30-minute window before start time
- **Email Notifications**: Confirmation and cancellation emails
- **Daily Limits**: Maximum 2 bookings per user per day

#### 🛠️ **Technical Improvements**
- **Database Schema**: Added `facilities` and `bookings` tables
- **API Endpoints**: New RESTful APIs for booking operations
- **Frontend Enhancements**: 
  - Interactive time slot grid with color-coding
  - Real-time form validation
  - Responsive design for all devices
- **Backend Logic**: 
  - Multi-slot validation algorithms
  - Conflict detection and prevention
  - Timezone synchronization (Malaysia UTC+8)

#### 📁 **New Files Added**
```
🏢 Booking System Files:
├── booking.php              # Multi-slot booking interface
├── process_booking.php      # Booking processing & validation
├── check_availability.php   # Real-time availability API
├── my_bookings.php         # Booking management interface
├── cancel_booking.php      # Booking cancellation handler
├── css/booking.css         # Booking system styles
├── js/booking.js          # Multi-slot booking logic
├── js/my_bookings.js      # Booking management scripts
├── create_facilities_table.sql  # Facilities database schema
├── create_bookings_table.sql    # Bookings database schema
└── BOOKING_SYSTEM_README.md     # Detailed system documentation
```

#### 🎯 **User Experience Improvements**
- **Intuitive Interface**: Click to select/deselect time slots
- **Visual Feedback**: Color-coded availability status
- **Smart Constraints**: Prevents invalid slot combinations
- **Instant Validation**: Real-time error checking and messaging
- **Mobile Responsive**: Full functionality on all device sizes

### **V0.2.0 - Core Authentication System** (May 2024)
- ✅ Student registration with INTI email validation
- ✅ OTP email verification system
- ✅ Secure login/logout functionality
- ✅ User dashboard and profile management
- ✅ PHPMailer integration for email services
- ✅ Responsive Bootstrap 5 UI design

---

## 🤝 **Contributing**

### Development Setup
```bash
# Clone the repository
git clone [repository-url]

# Install dependencies
composer install

# Set up development environment
cp .env.example .env
# Edit .env with your configuration

# Run development server
php -S localhost:8000
```

### Code Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Comment complex logic
- Write unit tests for new features

---

## 📝 **License & Credits**

### License
This project is licensed under the MIT License - see the LICENSE file for details.

### Credits
- **Developer**: zhiyang
- **Institution**: INTI International University
- **Framework**: Custom PHP Framework
- **Libraries**: PHPMailer, Bootstrap, jQuery

---

**📝 Version**: 0.3.1
**📅 Last Updated**: January 2025  
**👨‍💻 Developer**: Zhi Yang  
**🏢 Organization**: INTI International College 
**📧 Support**: xxd051030@gmail.com

---

## 🎯 **Recent Updates Summary (V0.3.1)**

### Admin Dashboard Enhancements
✅ **Enhanced Facility Filtering**: Smart visual filtering that maintains full table view  
✅ **UI Optimization**: Removed purpose column from time table for cleaner display  
✅ **Better UX**: Facility highlighting with semi-transparent non-selected items  
✅ **Performance**: Client-side filtering reduces server load  
✅ **Accessibility**: Maintained complete table structure during filtering operations