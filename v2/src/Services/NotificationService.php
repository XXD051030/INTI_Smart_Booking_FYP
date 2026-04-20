<?php

declare(strict_types=1);

namespace V2\Services;

use V2\Repositories\NotificationRepository;

final class NotificationService
{
    public function __construct(private readonly NotificationRepository $notifications)
    {
    }

    public function listForUser(int $userId, bool $onlyUnread = false): array
    {
        return $this->notifications->listForUser($userId, $onlyUnread);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notifications->unreadCount($userId);
    }

    public function markRead(int $notificationId, int $userId): void
    {
        $this->notifications->markRead($notificationId, $userId);
    }

    public function markAllRead(int $userId): void
    {
        $this->notifications->markAllRead($userId);
    }

    public function bookingConfirmed(int $userId, int $bookingId, string $facilityName, string $bookingDate, string $startTime, string $endTime): void
    {
        $title = 'Booking confirmed';
        $message = sprintf(
            '%s has been booked for %s, %s.',
            $facilityName,
            format_long_date($bookingDate),
            format_time_range($startTime, $endTime)
        );

        $this->notifications->create($userId, 'booking_confirmed', $title, $message, $bookingId);
    }

    public function bookingCancelled(int $userId, int $bookingId, string $facilityName, string $bookingDate, string $startTime, string $endTime): void
    {
        $title = 'Booking cancelled';
        $message = sprintf(
            '%s on %s, %s has been cancelled.',
            $facilityName,
            format_long_date($bookingDate),
            format_time_range($startTime, $endTime)
        );

        $this->notifications->create($userId, 'booking_cancelled', $title, $message, $bookingId);
    }

    public function systemNotice(int $userId, string $title, string $message): void
    {
        $this->notifications->create($userId, 'system_notice', $title, $message, null);
    }
}
