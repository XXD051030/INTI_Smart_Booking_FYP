# 🎓 INTI Student Registration & Login System

A complete student registration and login system with email verification functionality, specifically designed for INTI University students.

## 📋 **System Overview**

### 🎯 **Key Features**
- ✅ Student Registration (INTI email only)
- ✅ Email OTP Verification
- ✅ User Login/Logout
- ✅ Personal Dashboard
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
│   └── check.php               # System health check
│
├── 🎨 **Frontend Assets**
│   ├── css/
│   │   ├── style.css           # Main stylesheet
│   │   ├── login.css           # Login page styles
│   │   └── otp-verify.css      # OTP verification styles
│   ├── js/
│   │   ├── validations.js      # Form validation scripts
│   │   └── countdown.js        # OTP countdown timer
│   └── images/
│       ├── logo/               # Logo images
│       ├── place/              # Facility images
│       └── assets/             # Other resources
│
├── 📋 **Legal & Documentation**
│   ├── rules.php              # Terms and conditions
│   ├── README.md              # Project documentation
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
  - Reservation management (to be developed)
  - Account settings
  - Secure logout option

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
- [x] PHPMailer integration
- [x] Responsive design
- [x] Form validations

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

### 🔧 **Automated Testing Commands**
```bash
# Test database connection
php -r "include 'db.php'; echo 'Database: Connected successfully\n';"

# Test page accessibility
curl -I http://localhost/register.php
curl -I http://localhost/login.php

# Check Composer dependencies
composer validate
composer install --dry-run

# Test email configuration (create test_mail.php)
php test_mail.php
```

---

## 📈 **Future Development Roadmap**

### 🚀 **Phase 1: Core Features** (Completed)
- [x] User registration and authentication
- [x] Email verification system
- [x] Responsive UI design
- [x] Basic security measures

### 🏢 **Phase 2: Reservation System** (In Progress)
- [ ] Facility booking system
- [ ] Calendar integration
- [ ] Booking management
- [ ] Availability checking

### 🔧 **Phase 3: Advanced Features** (Planned)
- [ ] Admin dashboard
- [ ] Reporting system
- [ ] Notification system

### 🛡️ **Phase 4: Enterprise Features** (Future)
- [ ] Multi-language support
- [ ] Single Sign-On (SSO)
- [ ] LDAP integration
- [ ] Advanced analytics
- [ ] Backup and recovery system

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

**📝 Version**: 0.2.5
**📅 Last Updated**: June 2025  
**👨‍💻 Developer**: Zhi Yang  
**🏢 Organization**: INTI International College 
**📧 Support**: xxd051030@gmail.com