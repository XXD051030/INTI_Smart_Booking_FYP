<?php

declare(strict_types=1);

namespace V2\Repositories;

use PDO;

final class BookingRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function countRequestTokensForUserOnDate(int $userId, string $date): int
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT request_token) FROM bookings WHERE user_id = :user_id AND booking_date = :booking_date AND status = 'confirmed'"
        );
        $statement->execute([
            ':user_id' => $userId,
            ':booking_date' => $date,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function bookedSlotsForFacilityDate(int $facilityId, string $date): array
    {
        $statement = $this->pdo->prepare(
            "SELECT start_time FROM bookings WHERE facility_id = :facility_id AND booking_date = :booking_date AND status = 'confirmed'"
        );
        $statement->execute([
            ':facility_id' => $facilityId,
            ':booking_date' => $date,
        ]);

        return array_map(static fn (array $row): string => $row['start_time'], $statement->fetchAll());
    }

    public function createRequest(int $userId, int $facilityId, string $date, array $slots, string $purpose): array
    {
        $requestToken = bin2hex(random_bytes(16));
        $primaryBookingId = null;

        $statement = $this->pdo->prepare(
            "INSERT INTO bookings (request_token, user_id, facility_id, booking_date, start_time, end_time, purpose, status)
             VALUES (:request_token, :user_id, :facility_id, :booking_date, :start_time, :end_time, :purpose, 'confirmed')"
        );

        foreach ($slots as $slot) {
            $statement->execute([
                ':request_token' => $requestToken,
                ':user_id' => $userId,
                ':facility_id' => $facilityId,
                ':booking_date' => $date,
                ':start_time' => $slot['start_time'],
                ':end_time' => $slot['end_time'],
                ':purpose' => $purpose,
            ]);

            if ($primaryBookingId === null) {
                $primaryBookingId = (int) $this->pdo->lastInsertId();
            }
        }

        return [
            'request_token' => $requestToken,
            'primary_booking_id' => (int) $primaryBookingId,
        ];
    }

    public function groupedForUser(int $userId): array
    {
        $statement = $this->pdo->prepare($this->baseGroupedQuery() . ' WHERE b.user_id = :user_id GROUP BY b.request_token ORDER BY b.booking_date DESC, start_time DESC');
        $statement->execute([':user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function groupedForCalendar(int $userId, string $startDate, string $endDate): array
    {
        $statement = $this->pdo->prepare(
            $this->baseGroupedQuery() . ' WHERE b.user_id = :user_id AND b.booking_date BETWEEN :start_date AND :end_date GROUP BY b.request_token ORDER BY b.booking_date ASC, start_time ASC'
        );
        $statement->execute([
            ':user_id' => $userId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        return $statement->fetchAll();
    }

    public function groupedForAdmin(array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['date'])) {
            $conditions[] = 'b.booking_date = :booking_date';
            $params[':booking_date'] = $filters['date'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'b.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['facility_id'])) {
            $conditions[] = 'b.facility_id = :facility_id';
            $params[':facility_id'] = (int) $filters['facility_id'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(lower(u.display_name) LIKE :search OR lower(u.email) LIKE :search OR lower(f.name) LIKE :search)';
            $params[':search'] = '%' . strtolower((string) $filters['search']) . '%';
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $statement = $this->pdo->prepare($this->baseGroupedQuery() . $where . ' GROUP BY b.request_token ORDER BY b.booking_date DESC, start_time DESC');
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findGroupedByToken(string $requestToken, ?int $userId = null): ?array
    {
        $sql = $this->baseGroupedQuery() . ' WHERE b.request_token = :request_token';
        $params = [':request_token' => $requestToken];

        if ($userId !== null) {
            $sql .= ' AND b.user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $sql .= ' GROUP BY b.request_token LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() ?: null;
    }

    public function cancelRequest(string $requestToken): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE bookings SET status = 'cancelled', cancelled_at = CURRENT_TIMESTAMP WHERE request_token = :request_token AND status = 'confirmed'"
        );
        $statement->execute([':request_token' => $requestToken]);
    }

    public function countConfirmed(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(DISTINCT request_token) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
    }

    public function countCancelled(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(DISTINCT request_token) FROM bookings WHERE status = 'cancelled'")->fetchColumn();
    }

    public function countTotalRequests(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(DISTINCT request_token) FROM bookings')->fetchColumn();
    }

    private function baseGroupedQuery(): string
    {
        return "
            SELECT
                MIN(b.id) AS booking_id,
                b.request_token,
                b.user_id,
                u.display_name,
                u.email,
                b.facility_id,
                f.name AS facility_name,
                f.location,
                f.type AS facility_type,
                f.image_path,
                f.capacity,
                b.booking_date,
                MIN(b.start_time) AS start_time,
                MAX(b.end_time) AS end_time,
                b.purpose,
                CASE WHEN SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) > 0 THEN 'confirmed' ELSE 'cancelled' END AS status,
                COUNT(*) AS slot_count,
                MIN(b.created_at) AS created_at,
                MAX(b.cancelled_at) AS cancelled_at
            FROM bookings b
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN facilities f ON f.id = b.facility_id
        ";
    }
}
