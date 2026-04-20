<?php

declare(strict_types=1);

use V2\Support\AppContext;
use V2\Support\Auth;
use V2\Support\Csrf;
use V2\Support\Flash;

function config(string $key, mixed $default = null): mixed
{
    $config = $GLOBALS['v2_config'] ?? [];
    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function app(): AppContext
{
    return $GLOBALS['v2_app'];
}

function e(string|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $prefix = APP_BASE_URL ?: '/v2';
    $path = ltrim($path, '/');

    return $path === '' ? $prefix : $prefix . '/' . $path;
}

function admin_url(string $path = ''): string
{
    return app_url('admin/' . ltrim($path, '/'));
}

function action_url(string $path = ''): string
{
    return app_url('actions/' . ltrim($path, '/'));
}

function site_url(string $path = ''): string
{
    $root = PROJECT_BASE_URL;
    $path = ltrim($path, '/');

    return $path === ''
        ? ($root === '' ? '/' : $root)
        : (($root === '' ? '' : $root) . '/' . $path);
}

function asset_url(string $path = ''): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    if (preg_match('#^https?://#', $path) === 1 || str_starts_with($path, '/')) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . app_url($path));
    }
    exit;
}

function flash(string $key, string $message, string $type = 'success'): void
{
    Flash::set($key, ['message' => $message, 'type' => $type]);
}

function pull_flash(string $key): mixed
{
    return Flash::pull($key);
}

function old(string $key, mixed $default = ''): mixed
{
    static $oldInput;
    if ($oldInput === null) {
        $oldInput = Flash::pullOldInput();
    }

    return $oldInput[$key] ?? $default;
}

function remember_old_input(array $input): void
{
    Flash::setOldInput($input);
}

function clear_old_input(): void
{
    Flash::clearOldInput();
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_or_abort(): void
{
    $token = $_POST['_token'] ?? '';
    if (!Csrf::verify((string) $token)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
}

function current_student(): ?array
{
    return Auth::student();
}

function current_admin(): ?array
{
    return Auth::admin();
}

function require_student(): void
{
    if (!Auth::checkStudent()) {
        flash('auth', 'Please sign in to continue.', 'error');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!Auth::checkAdmin()) {
        flash('admin_auth', 'Please sign in to the admin portal.', 'error');
        redirect('admin/index.php');
    }
}

function student_id_from_email(string $email): string
{
    return strtoupper((string) strtok($email, '@'));
}

function format_booking_code(int $bookingId): string
{
    return 'BK' . str_pad((string) $bookingId, 6, '0', STR_PAD_LEFT);
}

function format_date_human(string $date): string
{
    return (new DateTimeImmutable($date))->format('j M Y');
}

function format_long_date(string $date): string
{
    return (new DateTimeImmutable($date))->format('l, j F Y');
}

function format_time_human(string $time): string
{
    return (new DateTimeImmutable('1970-01-01 ' . $time))->format('g:i A');
}

function format_time_range(string $startTime, string $endTime): string
{
    return format_time_human($startTime) . ' - ' . format_time_human($endTime);
}

function format_timestamp_human(string $timestamp): string
{
    return (new DateTimeImmutable($timestamp))->format('j M Y, g:i A');
}

function time_ago(string $timestamp): string
{
    $time = new DateTimeImmutable($timestamp);
    $now = new DateTimeImmutable();
    $diff = $now->getTimestamp() - $time->getTimestamp();

    if ($diff < 60) {
        return 'Just now';
    }

    if ($diff < 3600) {
        $minutes = (int) floor($diff / 60);
        return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
    }

    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }

    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    return format_timestamp_human($timestamp);
}

function booking_display_status(array $booking): string
{
    if (($booking['status'] ?? 'confirmed') === 'cancelled') {
        return 'Cancelled';
    }

    $bookingStart = new DateTimeImmutable(($booking['booking_date'] ?? '') . ' ' . ($booking['start_time'] ?? '00:00'));
    if ($bookingStart < new DateTimeImmutable()) {
        return 'Completed';
    }

    return 'Confirmed';
}

function booking_status_class(array $booking): string
{
    return match (booking_display_status($booking)) {
        'Cancelled' => 'is-cancelled',
        'Completed' => 'is-complete',
        default => 'is-confirmed',
    };
}

function query_path(array $changes = []): string
{
    $query = array_merge($_GET, $changes);
    foreach ($query as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        }
    }

    $base = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
    $queryString = http_build_query($query);

    return $queryString === '' ? $base : $base . '?' . $queryString;
}
