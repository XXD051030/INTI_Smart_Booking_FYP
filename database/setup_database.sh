#!/bin/bash

# INTI Smart Booking System - Database Setup Script (Shell Version)
# 
# This script automatically sets up the complete database structure
# for the INTI Student Registration & Facility Booking System
# 
# Usage: chmod +x setup_database.sh && ./setup_database.sh

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="reservation_system"
DB_USER="webapp"
DB_PASS="webapp123"
MYSQL_ROOT_USER="root"
MYSQL_ROOT_PASS=""  # XAMPP default has no root password
MYSQL_PATH="/Applications/XAMPP/xamppfiles/bin/mysql"

# Function to print colored messages
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

print_header() {
    echo
    echo "============================================================"
    print_message $BLUE "$1"
    echo "============================================================"
}

# Check if MySQL is accessible
check_mysql() {
    print_header "🔍 Checking MySQL Connection"
    
    if ! command -v $MYSQL_PATH &> /dev/null; then
        print_message $RED "❌ MySQL not found at $MYSQL_PATH"
        print_message $YELLOW "Please make sure XAMPP is installed and MySQL is running"
        exit 1
    fi
    
    if ! $MYSQL_PATH -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASS -e "SELECT 1;" &> /dev/null; then
        print_message $RED "❌ Cannot connect to MySQL as root"
        print_message $YELLOW "Please make sure MySQL is running and the root password is correct"
        exit 1
    fi
    
    print_message $GREEN "✅ MySQL connection successful"
}

# Create database and user
setup_database_user() {
    print_header "🗄️  Setting Up Database and User"
    
    print_message $YELLOW "📊 Creating database: $DB_NAME..."
    $MYSQL_PATH -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    if [ $? -eq 0 ]; then
        print_message $GREEN "✅ Database created successfully"
    else
        print_message $RED "❌ Failed to create database"
        exit 1
    fi
    
    print_message $YELLOW "👤 Creating user: $DB_USER..."
    $MYSQL_PATH -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASS -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    
    print_message $YELLOW "🔑 Granting privileges..."
    $MYSQL_PATH -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASS -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    $MYSQL_PATH -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASS -e "FLUSH PRIVILEGES;"
    
    if [ $? -eq 0 ]; then
        print_message $GREEN "✅ User and privileges setup completed"
    else
        print_message $RED "❌ Failed to setup user privileges"
        exit 1
    fi
}

# Import SQL files
import_sql_files() {
    print_header "📋 Importing Table Structures"
    
    SQL_FILES=(
        "create_users_table.sql"
        "create_facilities_table.sql" 
        "create_bookings_table.sql"
        "create_otp_table.sql"
        "create_notifications_table.sql"
    )
    
    for sql_file in "${SQL_FILES[@]}"; do
        if [ -f "$sql_file" ]; then
            print_message $YELLOW "📄 Importing: $sql_file..."
            $MYSQL_PATH -u $DB_USER -p$DB_PASS $DB_NAME < "$sql_file"
            
            if [ $? -eq 0 ]; then
                print_message $GREEN "✅ $sql_file imported successfully"
            else
                print_message $RED "❌ Failed to import $sql_file"
                # Continue with other files
            fi
        else
            print_message $RED "❌ SQL file not found: $sql_file"
        fi
    done
}

# Verify setup
verify_setup() {
    print_header "🔍 Verifying Database Setup"
    
    print_message $YELLOW "🔗 Testing database connection..."
    
    # Test connection
    if $MYSQL_PATH -u $DB_USER -p$DB_PASS -e "USE $DB_NAME; SELECT 1;" &> /dev/null; then
        print_message $GREEN "✅ Database connection successful"
    else
        print_message $RED "❌ Database connection failed"
        exit 1
    fi
    
    # Check tables
    tables=$($MYSQL_PATH -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" 2>/dev/null | tail -n +2)
    
    expected_tables=("users" "facilities" "bookings" "user_otp" "notifications")
    
    for table in "${expected_tables[@]}"; do
        if echo "$tables" | grep -q "^$table$"; then
            count=$($MYSQL_PATH -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM $table;" 2>/dev/null | tail -n +2)
            print_message $BLUE "📊 Table '$table': $count records"
        else
            print_message $RED "❌ Missing table: $table"
        fi
    done
}

# Main execution
main() {
    print_message $BLUE "🎓 INTI Smart Booking System - Database Setup"
    echo
    print_message $BLUE "Database: $DB_NAME"
    print_message $BLUE "User: $DB_USER"
    
    check_mysql
    setup_database_user
    import_sql_files
    verify_setup
    
    print_header "🎉 Setup Complete!"
    print_message $GREEN "Database setup completed successfully!"
    print_message $GREEN "Your INTI Smart Booking System is now ready to use."
    print_message $BLUE "You can now access the application in your web browser."
    
    echo
    print_message $YELLOW "Database Configuration:"
    print_message $BLUE "• Database: $DB_NAME"
    print_message $BLUE "• Username: $DB_USER"
    print_message $BLUE "• Password: $DB_PASS"
    print_message $BLUE "• Host: localhost"
    echo
}

# Check if script is being sourced or executed
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main
fi