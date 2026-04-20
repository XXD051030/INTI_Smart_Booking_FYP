# INTI Booking System V2

Parallel rewrite of the original INTI booking project.

## Stack

- PHP 8+
- SQLite via PDO
- Multi-page SSR
- Custom CSS/JS

## Entry Points

- Student portal: `v2/login.php`
- Student register: `v2/register.php`
- Admin portal: `v2/admin/index.php`

## Default Admin Credentials

- Username: `admin`
- Password: `admin123`

The default admin account is seeded automatically the first time the SQLite database is initialized.

## Storage

- SQLite database file: `v2/storage/database/app.sqlite`
- Mail log placeholder: `v2/storage/logs/mail.log`

## Notes

- OTP is intentionally removed in V2 first release.
- Student registration only accepts `@student.newinti.edu.my`.
- Stationary in-app notifications are active.
- Mail delivery is only a stub for now and remains disabled by default.
