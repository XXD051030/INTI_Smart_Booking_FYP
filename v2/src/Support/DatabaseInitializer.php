<?php

declare(strict_types=1);

namespace V2\Support;

use PDO;

final class DatabaseInitializer
{
    public static function initialize(PDO $pdo, array $config, bool $needsInitialization): void
    {
        $schema = file_get_contents(APP_ROOT . '/storage/database/schema.sql');
        if ($schema === false) {
            throw new \RuntimeException('Unable to read SQLite schema.');
        }

        $pdo->exec($schema);

        self::seedFacilities($pdo);
        self::seedAdmin($pdo, $config);

        if ($needsInitialization) {
            $pdo->exec('VACUUM;');
        }
    }

    private static function seedFacilities(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM facilities')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $seedFacilities = [
            [
                'name' => 'Discussion Room A',
                'slug' => 'discussion-room-a',
                'type' => 'discussion_room',
                'description' => 'Quiet collaborative space for focused group work and presentation prep.',
                'capacity' => 8,
                'location' => 'Library Level 2',
                'image_path' => '/images/place/discussion_room.jpg',
                'advance_booking_days' => 0,
            ],
            [
                'name' => 'Discussion Room B',
                'slug' => 'discussion-room-b',
                'type' => 'discussion_room',
                'description' => 'Mid-size room with display support for student meetings and review sessions.',
                'capacity' => 12,
                'location' => 'Library Level 3',
                'image_path' => '/images/place/discussion_room.jpg',
                'advance_booking_days' => 0,
            ],
            [
                'name' => 'Discussion Room C',
                'slug' => 'discussion-room-c',
                'type' => 'discussion_room',
                'description' => 'Larger room for workshops, project stand-ups, and faculty-supervised sessions.',
                'capacity' => 16,
                'location' => 'Main Building Level 4',
                'image_path' => '/images/place/discussion_room.jpg',
                'advance_booking_days' => 0,
            ],
            [
                'name' => 'Basketball Court',
                'slug' => 'basketball-court',
                'type' => 'basketball_court',
                'description' => 'Outdoor sports facility for practice, club sessions, and friendly games.',
                'capacity' => 20,
                'location' => 'Sports Complex',
                'image_path' => '/images/place/basketball_court.jpg',
                'advance_booking_days' => 7,
            ],
            [
                'name' => 'STEM Lab A',
                'slug' => 'stem-lab-a',
                'type' => 'stem_lab',
                'description' => 'Hands-on lab for engineering collaboration, demos, and technical project work.',
                'capacity' => 25,
                'location' => 'Engineering Building Level 2',
                'image_path' => '/images/place/stem_lab.jpg',
                'advance_booking_days' => 1,
            ],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO facilities
                (name, slug, type, description, capacity, location, image_path, advance_booking_days, operating_start_time, operating_end_time, is_active)
             VALUES
                (:name, :slug, :type, :description, :capacity, :location, :image_path, :advance_booking_days, :operating_start_time, :operating_end_time, :is_active)'
        );

        foreach ($seedFacilities as $facility) {
            $statement->execute([
                ':name' => $facility['name'],
                ':slug' => $facility['slug'],
                ':type' => $facility['type'],
                ':description' => $facility['description'],
                ':capacity' => $facility['capacity'],
                ':location' => $facility['location'],
                ':image_path' => $facility['image_path'],
                ':advance_booking_days' => $facility['advance_booking_days'],
                ':operating_start_time' => '08:00',
                ':operating_end_time' => '17:00',
                ':is_active' => 1,
            ]);
        }
    }

    private static function seedAdmin(PDO $pdo, array $config): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $admin = $config['defaults']['admin'] ?? [];
        $statement = $pdo->prepare(
            'INSERT INTO admin_users (username, display_name, email, password_hash) VALUES (:username, :display_name, :email, :password_hash)'
        );
        $statement->execute([
            ':username' => (string) ($admin['username'] ?? 'admin'),
            ':display_name' => (string) ($admin['display_name'] ?? 'System Admin'),
            ':email' => (string) ($admin['email'] ?? 'admin@inti.local'),
            ':password_hash' => password_hash((string) ($admin['password'] ?? 'admin123'), PASSWORD_DEFAULT),
        ]);
    }
}
