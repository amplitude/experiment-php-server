<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Logger;

use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel as PsrLogLevel;

/**
 * A default logger implementation that logs to error_log.
 */
class DefaultLogger extends AbstractLogger
{
    private const LEVEL_MAP = [
        PsrLogLevel::EMERGENCY => LogLevel::EMERGENCY,
        PsrLogLevel::ALERT => LogLevel::ALERT,
        PsrLogLevel::CRITICAL => LogLevel::CRITICAL,
        PsrLogLevel::ERROR => LogLevel::ERROR,
        PsrLogLevel::WARNING => LogLevel::WARNING,
        PsrLogLevel::NOTICE => LogLevel::NOTICE,
        PsrLogLevel::INFO => LogLevel::INFO,
        PsrLogLevel::DEBUG => LogLevel::DEBUG,
    ];

    /**
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        $intLevel = self::LEVEL_MAP[$level] ?? null;
        if ($intLevel === null) {
            return;
        }

        $date = new DateTimeImmutable();
        $timestamp = $date->format('Y-m-d\\TH:i:sP');
        $levelString = LogLevel::toString($intLevel);
        $logMessage = "[$timestamp] AmplitudeExperiment.$levelString: $message";
        error_log($logMessage);
    }
}
