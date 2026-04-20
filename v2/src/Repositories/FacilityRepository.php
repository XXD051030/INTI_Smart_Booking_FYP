<?php

declare(strict_types=1);

namespace V2\Repositories;

use PDO;

final class FacilityRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function allActive(): array
    {
        $statement = $this->pdo->query('SELECT * FROM facilities WHERE is_active = 1 ORDER BY type, name');
        return $statement->fetchAll();
    }

    public function findActiveById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM facilities WHERE id = :id AND is_active = 1 LIMIT 1');
        $statement->execute([':id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function countActive(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM facilities WHERE is_active = 1')->fetchColumn();
    }
}
