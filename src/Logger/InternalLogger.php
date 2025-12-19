<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel as PsrLogLevel;

/**
 * A logger wrapper that filters messages by log level.
 */
class InternalLogger extends AbstractLogger
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

    private LoggerInterface $logger;
    private int $logLevel;

    public function __construct(LoggerInterface $logger, int $logLevel)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

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

        if ($intLevel <= $this->logLevel) {
            $this->logger->log($level, (string) $message, $context);
        }
    }
}
