<?php

declare(strict_types=1);

namespace V2\Services;

final class MailService
{
    public function __construct(private readonly array $config)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $logFile = (string) ($this->config['log_file'] ?? APP_ROOT . '/storage/logs/mail.log');
        $directory = dirname($logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $payload = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n%s\n\n",
            date('c'),
            $to,
            $subject,
            $body
        );

        file_put_contents($logFile, $payload, FILE_APPEND);

        return true;
    }
}
