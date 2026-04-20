<?php

declare(strict_types=1);

namespace V2\Services;

use V2\Repositories\AdminUserRepository;
use V2\Support\Auth;

final class AdminAuthService
{
    public function __construct(private readonly AdminUserRepository $admins)
    {
    }

    public function login(string $username, string $password): array
    {
        $admin = $this->admins->findByUsername(trim($username));

        if ($admin === null || !password_verify($password, $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid admin credentials.'];
        }

        Auth::loginAdmin($admin);

        return ['success' => true, 'message' => 'Admin session started.'];
    }
}
