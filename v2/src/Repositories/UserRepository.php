<?php

declare(strict_types=1);

namespace V2\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE lower(email) = lower(:email) LIMIT 1');
        $statement->execute([':email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function create(string $displayName, string $email, string $passwordHash, string $language = 'en'): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (display_name, email, password_hash, preferred_language) VALUES (:display_name, :email, :password_hash, :preferred_language)'
        );
        $statement->execute([
            ':display_name' => $displayName,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':preferred_language' => $language,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, string $displayName, string $email, string $language): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET display_name = :display_name, email = :email, preferred_language = :preferred_language, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $id,
            ':display_name' => $displayName,
            ':email' => $email,
            ':preferred_language' => $language,
        ]);
    }

    public function updateLanguage(int $id, string $language): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET preferred_language = :preferred_language, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute([
            ':id' => $id,
            ':preferred_language' => $language,
        ]);
    }

    public function resetPassword(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute([
            ':id' => $id,
            ':password_hash' => $passwordHash,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function all(?string $search = null): array
    {
        $sql = 'SELECT * FROM users';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE lower(display_name) LIKE :search OR lower(email) LIKE :search';
            $params[':search'] = '%' . strtolower($search) . '%';
        }

        $sql .= ' ORDER BY created_at DESC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
