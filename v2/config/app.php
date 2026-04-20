<?php

declare(strict_types=1);

return [
    'name' => 'INTI Booking System V2',
    'timezone' => 'Asia/Kuala_Lumpur',
    'student_email_domain' => 'student.newinti.edu.my',
    'base_url' => null,
    'booking' => [
        'max_request_count_per_day' => 2,
        'max_consecutive_slots' => 2,
        'cancel_buffer_minutes' => 30,
        'slot_length_minutes' => 60,
    ],
    'mail' => [
        'enabled' => false,
        'log_file' => __DIR__ . '/../storage/logs/mail.log',
    ],
    'defaults' => [
        'admin' => [
            'username' => 'admin',
            'display_name' => 'System Admin',
            'email' => 'admin@inti.local',
            'password' => 'admin123',
        ],
        'language' => 'en',
    ],
];
