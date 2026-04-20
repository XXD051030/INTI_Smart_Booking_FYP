<?php

declare(strict_types=1);

namespace V2\Support;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $databasePath = APP_ROOT . '/storage/database/app.sqlite';
        $databaseDirectory = dirname($databasePath);

        if (!is_dir($databaseDirectory)) {
            mkdir($databaseDirectory, 0775, true);
        }

        $needsInitialization = !file_exists($databasePath);

        self::$connection = new PDO('sqlite:' . $databasePath);
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$connection->exec('PRAGMA foreign_keys = ON;');

        DatabaseInitializer::initialize(self::$connection, $config, $needsInitialization);

        return self::$connection;
    }
}
