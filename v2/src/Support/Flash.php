<?php

declare(strict_types=1);

namespace V2\Support;

final class Flash
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION['_flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function setOldInput(array $input): void
    {
        $_SESSION['_old'] = $input;
    }

    public static function oldInput(): array
    {
        $old = $_SESSION['_old'] ?? [];
        return is_array($old) ? $old : [];
    }

    public static function pullOldInput(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        return is_array($old) ? $old : [];
    }

    public static function clearOldInput(): void
    {
        unset($_SESSION['_old']);
    }
}
