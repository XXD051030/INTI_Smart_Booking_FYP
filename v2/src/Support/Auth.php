<?php

declare(strict_types=1);

namespace V2\Support;

final class Auth
{
    private const STUDENT_KEY = 'v2_student';
    private const ADMIN_KEY = 'v2_admin';

    public static function loginStudent(array $user): void
    {
        $_SESSION[self::STUDENT_KEY] = [
            'id' => (int) $user['id'],
            'display_name' => $user['display_name'],
            'email' => $user['email'],
            'preferred_language' => $user['preferred_language'] ?? 'en',
        ];
    }

    public static function loginAdmin(array $admin): void
    {
        $_SESSION[self::ADMIN_KEY] = [
            'id' => (int) $admin['id'],
            'username' => $admin['username'],
            'display_name' => $admin['display_name'],
            'email' => $admin['email'],
        ];
    }

    public static function student(): ?array
    {
        return $_SESSION[self::STUDENT_KEY] ?? null;
    }

    public static function admin(): ?array
    {
        return $_SESSION[self::ADMIN_KEY] ?? null;
    }

    public static function checkStudent(): bool
    {
        return isset($_SESSION[self::STUDENT_KEY]['id']);
    }

    public static function checkAdmin(): bool
    {
        return isset($_SESSION[self::ADMIN_KEY]['id']);
    }

    public static function logoutStudent(): void
    {
        unset($_SESSION[self::STUDENT_KEY]);
    }

    public static function logoutAdmin(): void
    {
        unset($_SESSION[self::ADMIN_KEY]);
    }
}
