<?php

declare(strict_types=1);

namespace V2\Support;

use PDO;
use V2\Repositories\AdminUserRepository;
use V2\Repositories\BookingRepository;
use V2\Repositories\FacilityRepository;
use V2\Repositories\NotificationRepository;
use V2\Repositories\UserRepository;
use V2\Services\AdminAuthService;
use V2\Services\BookingService;
use V2\Services\MailService;
use V2\Services\NotificationService;
use V2\Services\StudentAuthService;

final class AppContext
{
    private PDO $pdo;
    private array $config;
    private View $view;
    private UserRepository $users;
    private AdminUserRepository $admins;
    private FacilityRepository $facilities;
    private BookingRepository $bookings;
    private NotificationRepository $notifications;
    private MailService $mail;
    private NotificationService $notificationService;
    private StudentAuthService $studentAuth;
    private AdminAuthService $adminAuth;
    private BookingService $bookingService;

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = Database::connection($config);
        $this->view = new View();
        $this->users = new UserRepository($this->pdo);
        $this->admins = new AdminUserRepository($this->pdo);
        $this->facilities = new FacilityRepository($this->pdo);
        $this->bookings = new BookingRepository($this->pdo);
        $this->notifications = new NotificationRepository($this->pdo);
        $this->mail = new MailService($config['mail'] ?? []);
        $this->notificationService = new NotificationService($this->notifications);
        $this->studentAuth = new StudentAuthService($this->users, $config);
        $this->adminAuth = new AdminAuthService($this->admins);
        $this->bookingService = new BookingService(
            $this->pdo,
            $this->config,
            $this->facilities,
            $this->bookings,
            $this->notificationService,
            $this->mail
        );
    }

    public static function boot(array $config): self
    {
        static $instance;
        if ($instance instanceof self) {
            return $instance;
        }

        $instance = new self($config);

        return $instance;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function view(): View
    {
        return $this->view;
    }

    public function users(): UserRepository
    {
        return $this->users;
    }

    public function admins(): AdminUserRepository
    {
        return $this->admins;
    }

    public function facilities(): FacilityRepository
    {
        return $this->facilities;
    }

    public function bookings(): BookingRepository
    {
        return $this->bookings;
    }

    public function notifications(): NotificationRepository
    {
        return $this->notifications;
    }

    public function mail(): MailService
    {
        return $this->mail;
    }

    public function notificationService(): NotificationService
    {
        return $this->notificationService;
    }

    public function studentAuth(): StudentAuthService
    {
        return $this->studentAuth;
    }

    public function adminAuth(): AdminAuthService
    {
        return $this->adminAuth;
    }

    public function bookingService(): BookingService
    {
        return $this->bookingService;
    }
}
