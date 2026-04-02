<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class DefaultLogger extends AbstractLogger
{
    private string $minLevel;

    private const LEVELS = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    ];

    public function __construct(string $minLevel = LogLevel::ERROR)
    {
        if (!isset(self::LEVELS[$minLevel])) {
            throw new InvalidArgumentException("Unknown log level: $minLevel");
        }
        $this->minLevel = $minLevel;
    }

    /**
     * @param string $level
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        if (!isset(self::LEVELS[$level])) {
            throw new InvalidArgumentException("Unknown log level: $level");
        }
        if (self::LEVELS[$level] > self::LEVELS[$this->minLevel]) {
            return;
        }
        $timestamp = date('Y-m-d\TH:i:sP');
        error_log("[$timestamp] AmplitudeExperiment.$level: " . (string) $message);
    }
}
