<?php

declare(strict_types=1);

namespace V2\Repositories;

use PDO;

final class NotificationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $userId, string $type, string $title, string $message, ?int $relatedBookingId = null): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, type, title, message, related_booking_id) VALUES (:user_id, :type, :title, :message, :related_booking_id)'
        );
        $statement->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':title' => $title,
            ':message' => $message,
            ':related_booking_id' => $relatedBookingId,
        ]);
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $statement->execute([':user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function listForUser(int $userId, bool $onlyUnread = false): array
    {
        $sql = 'SELECT * FROM notifications WHERE user_id = :user_id';
        if ($onlyUnread) {
            $sql .= ' AND is_read = 0';
        }
        $sql .= ' ORDER BY created_at DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function markRead(int $notificationId, int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            ':id' => $notificationId,
            ':user_id' => $userId,
        ]);
    }

    public function markAllRead(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = :user_id AND is_read = 0'
        );
        $statement->execute([':user_id' => $userId]);
    }
}
