<?php

declare(strict_types=1);

namespace V2\Repositories;

use PDO;

final class AdminUserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE lower(username) = lower(:username) LIMIT 1');
        $statement->execute([':username' => $username]);

        return $statement->fetch() ?: null;
    }
}
