<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Logger;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class DefaultLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        self::logMessage(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        self::logMessage(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        self::logMessage(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        self::logMessage(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        self::logMessage(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        self::logMessage(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        self::logMessage(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        self::logMessage(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        // Do nothing, only the leveled methods should be used.
    }

    private static function logMessage(int $level, string $message, array $context = []): void
    {
        $date = new DateTimeImmutable();
        $timestamp = $date->format('Y-m-d\\TH:i:sP');
        $level = LogLevel::toString($level);
        $message = "[$timestamp] AmplitudeExperiment.$level: $message";
        error_log($message);
    }
}
