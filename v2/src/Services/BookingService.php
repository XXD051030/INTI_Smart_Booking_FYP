<?php

declare(strict_types=1);

namespace V2\Services;

use DateInterval;
use DateTimeImmutable;
use PDO;
use Throwable;
use V2\Repositories\BookingRepository;
use V2\Repositories\FacilityRepository;

final class BookingService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $config,
        private readonly FacilityRepository $facilities,
        private readonly BookingRepository $bookings,
        private readonly NotificationService $notifications,
        private readonly MailService $mail
    ) {
    }

    public function availability(int $facilityId, string $date): array
    {
        $facility = $this->facilities->findActiveById($facilityId);
        if ($facility === null) {
            return [];
        }

        $slots = $this->timeSlotsForFacility($facility);
        $bookedSlots = $this->bookings->bookedSlotsForFacilityDate($facilityId, $date);
        $now = new DateTimeImmutable();
        $today = $now->format('Y-m-d');
        $available = [];

        foreach ($slots as $slot) {
            $isPast = $date === $today && $slot['start_time'] <= $now->format('H:i');
            $available[] = [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'available' => !$isPast && !in_array($slot['start_time'], $bookedSlots, true),
            ];
        }

        return $available;
    }

    public function createStudentBooking(int $userId, int $facilityId, string $date, array $selectedSlots, string $purpose, string $studentEmail): array
    {
        $facility = $this->facilities->findActiveById($facilityId);
        if ($facility === null) {
            return ['success' => false, 'message' => 'Facility not found.'];
        }

        $purpose = trim($purpose);
        if ($purpose === '' || strlen($purpose) < 10 || strlen($purpose) > 500) {
            return ['success' => false, 'message' => 'Purpose must be between 10 and 500 characters.'];
        }

        if (!$this->isValidDate($date)) {
            return ['success' => false, 'message' => 'Please choose a valid date.'];
        }

        $selectedSlots = array_values(array_unique(array_filter($selectedSlots)));
        sort($selectedSlots);

        if ($selectedSlots === []) {
            return ['success' => false, 'message' => 'Please choose at least one slot.'];
        }

        $maxSlots = (int) ($this->config['booking']['max_consecutive_slots'] ?? 2);
        if (count($selectedSlots) > $maxSlots) {
            return ['success' => false, 'message' => 'You can only book up to 2 consecutive slots.'];
        }

        $timeSlots = $this->timeSlotsForFacility($facility);
        $validStartTimes = array_column($timeSlots, 'start_time');

        foreach ($selectedSlots as $slot) {
            if (!in_array($slot, $validStartTimes, true)) {
                return ['success' => false, 'message' => 'One of the selected slots is invalid.'];
            }
        }

        if (!$this->areConsecutive($selectedSlots, $validStartTimes)) {
            return ['success' => false, 'message' => 'Selected slots must be consecutive.'];
        }

        $today = new DateTimeImmutable('today');
        $bookingDate = new DateTimeImmutable($date);
        if ($bookingDate < $today) {
            return ['success' => false, 'message' => 'Past dates cannot be booked.'];
        }

        $advanceLimit = $today->add(new DateInterval('P' . (int) $facility['advance_booking_days'] . 'D'));
        if ($bookingDate > $advanceLimit) {
            return ['success' => false, 'message' => 'This facility cannot be booked that far in advance.'];
        }

        $now = new DateTimeImmutable();
        foreach ($selectedSlots as $slot) {
            $slotDateTime = new DateTimeImmutable($date . ' ' . $slot);
            if ($slotDateTime <= $now) {
                return ['success' => false, 'message' => 'Past time slots cannot be booked.'];
            }
        }

        $dailyLimit = (int) ($this->config['booking']['max_request_count_per_day'] ?? 2);
        if ($this->bookings->countRequestTokensForUserOnDate($userId, $date) >= $dailyLimit) {
            return ['success' => false, 'message' => 'You have reached your booking request limit for that day.'];
        }

        $conflicts = array_intersect($selectedSlots, $this->bookings->bookedSlotsForFacilityDate($facilityId, $date));
        if ($conflicts !== []) {
            return ['success' => false, 'message' => 'One or more selected slots are already booked.'];
        }

        $slotsToCreate = [];
        foreach ($selectedSlots as $slot) {
            $slotIndex = array_search($slot, $validStartTimes, true);
            $slotsToCreate[] = [
                'start_time' => $slot,
                'end_time' => $timeSlots[$slotIndex]['end_time'],
            ];
        }

        $this->pdo->beginTransaction();

        try {
            $created = $this->bookings->createRequest($userId, $facilityId, $date, $slotsToCreate, $purpose);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return ['success' => false, 'message' => 'Booking could not be created. Please try again.'];
        }

        $startTime = $slotsToCreate[0]['start_time'];
        $endTime = $slotsToCreate[array_key_last($slotsToCreate)]['end_time'];

        $this->notifications->bookingConfirmed(
            $userId,
            $created['primary_booking_id'],
            $facility['name'],
            $date,
            $startTime,
            $endTime
        );

        $this->mail->send(
            $studentEmail,
            'Booking confirmed',
            sprintf('%s booked for %s, %s.', $facility['name'], format_long_date($date), format_time_range($startTime, $endTime))
        );

        return [
            'success' => true,
            'message' => 'Booking created successfully.',
            'booking_id' => $created['primary_booking_id'],
            'request_token' => $created['request_token'],
        ];
    }

    public function cancelStudentRequest(string $requestToken, int $userId, string $studentEmail): array
    {
        $booking = $this->bookings->findGroupedByToken($requestToken, $userId);
        if ($booking === null) {
            return ['success' => false, 'message' => 'Booking request not found.'];
        }

        return $this->cancelRequest($booking, $studentEmail, true);
    }

    public function cancelAdminRequest(string $requestToken, ?string $recipientEmail = null): array
    {
        $booking = $this->bookings->findGroupedByToken($requestToken);
        if ($booking === null) {
            return ['success' => false, 'message' => 'Booking request not found.'];
        }

        return $this->cancelRequest($booking, $recipientEmail ?? $booking['email'], false);
    }

    public function timeSlotsForFacility(array $facility): array
    {
        $slots = [];
        $slotLength = (int) ($this->config['booking']['slot_length_minutes'] ?? 60);
        $start = new DateTimeImmutable('1970-01-01 ' . $facility['operating_start_time']);
        $end = new DateTimeImmutable('1970-01-01 ' . $facility['operating_end_time']);

        while ($start < $end) {
            $slotEnd = $start->add(new DateInterval('PT' . $slotLength . 'M'));
            if ($slotEnd > $end) {
                break;
            }

            $slots[] = [
                'start_time' => $start->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
            ];
            $start = $slotEnd;
        }

        return $slots;
    }

    private function areConsecutive(array $selectedSlots, array $validSlots): bool
    {
        if (count($selectedSlots) <= 1) {
            return true;
        }

        for ($index = 1, $length = count($selectedSlots); $index < $length; $index++) {
            $previousPosition = array_search($selectedSlots[$index - 1], $validSlots, true);
            $currentPosition = array_search($selectedSlots[$index], $validSlots, true);

            if ($previousPosition === false || $currentPosition === false || $currentPosition - $previousPosition !== 1) {
                return false;
            }
        }

        return true;
    }

    private function cancelRequest(array $booking, string $email, bool $enforceBuffer): array
    {
        if (($booking['status'] ?? 'confirmed') === 'cancelled') {
            return ['success' => false, 'message' => 'This booking has already been cancelled.'];
        }

        $now = new DateTimeImmutable();
        $startAt = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time']);

        if ($startAt <= $now) {
            return ['success' => false, 'message' => 'Past bookings cannot be cancelled.'];
        }

        $bufferMinutes = (int) ($this->config['booking']['cancel_buffer_minutes'] ?? 30);
        if ($enforceBuffer && $startAt->getTimestamp() - $now->getTimestamp() <= ($bufferMinutes * 60)) {
            return ['success' => false, 'message' => 'Bookings cannot be cancelled within 30 minutes of the start time.'];
        }

        $this->bookings->cancelRequest($booking['request_token']);

        $this->notifications->bookingCancelled(
            (int) $booking['user_id'],
            (int) $booking['booking_id'],
            $booking['facility_name'],
            $booking['booking_date'],
            $booking['start_time'],
            $booking['end_time']
        );

        $this->mail->send(
            $email,
            'Booking cancelled',
            sprintf('%s on %s, %s was cancelled.', $booking['facility_name'], format_long_date($booking['booking_date']), format_time_range($booking['start_time'], $booking['end_time']))
        );

        return ['success' => true, 'message' => 'Booking cancelled successfully.'];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
