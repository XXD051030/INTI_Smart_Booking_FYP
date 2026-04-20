<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

require_admin();

$currentAdmin = current_admin();
$search = trim((string) ($_GET['search'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_abort();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'edit_user') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $displayName = trim((string) ($_POST['display_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $user = app()->users()->findById($userId);

        if ($user === null || $displayName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('message', 'Invalid user update payload.', 'error');
        } elseif (!str_ends_with($email, '@' . config('student_email_domain'))) {
            flash('message', 'User email must remain an INTI student address.', 'error');
        } else {
            $existing = app()->users()->findByEmail($email);
            if ($existing !== null && (int) $existing['id'] !== $userId) {
                flash('message', 'That email is already in use.', 'error');
            } else {
                app()->users()->updateProfile($userId, $displayName, $email, $user['preferred_language']);
                flash('message', 'User updated successfully.');
            }
        }
        redirect('admin/dashboard.php' . ($search !== '' ? '?search=' . urlencode($search) : ''));
    }

    if ($action === 'delete_user') {
        app()->users()->delete((int) ($_POST['user_id'] ?? 0));
        flash('message', 'User deleted successfully.');
        redirect('admin/dashboard.php' . ($search !== '' ? '?search=' . urlencode($search) : ''));
    }

    if ($action === 'reset_password') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = (string) ($_POST['new_password'] ?? '');
        if (strlen($newPassword) < 8) {
            flash('message', 'Password must be at least 8 characters.', 'error');
        } else {
            app()->users()->resetPassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
            flash('message', 'User password reset successfully.');
        }
        redirect('admin/dashboard.php' . ($search !== '' ? '?search=' . urlencode($search) : ''));
    }
}

$users = app()->users()->all($search !== '' ? $search : null);
$editingUser = isset($_GET['edit']) ? app()->users()->findById((int) $_GET['edit']) : null;

app()->view()->render('admin/dashboard', [
    'pageTitle' => 'Admin Dashboard',
    'pageHeading' => 'Admin Dashboard',
    'activeNav' => 'dashboard',
    'currentAdmin' => $currentAdmin,
    'users' => $users,
    'editingUser' => $editingUser,
    'search' => $search,
    'stats' => [
        'users' => app()->users()->count(),
        'bookings' => app()->bookings()->countTotalRequests(),
        'confirmed' => app()->bookings()->countConfirmed(),
        'facilities' => app()->facilities()->countActive(),
    ],
    'pageScripts' => ['admin.js'],
], 'admin');
