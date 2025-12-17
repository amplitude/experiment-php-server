<?php

namespace AmplitudeExperiment\Logger;

use Psr\Log\LoggerInterface;

class InternalLogger implements LoggerInterface
{
    private LoggerInterface $logger;
    private int $logLevel;

    public function __construct(LoggerInterface $logger, int $logLevel)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    public function emergency($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::EMERGENCY)) {
            $this->logger->emergency((string) $message, $context);
        }
    }

    public function alert($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::ALERT)) {
            $this->logger->alert((string) $message, $context);
        }
    }

    public function critical($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::CRITICAL)) {
            $this->logger->critical((string) $message, $context);
        }
    }

    public function error($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::ERROR)) {
            $this->logger->error((string) $message, $context);
        }
    }

    public function warning($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::WARNING)) {
            $this->logger->warning((string) $message, $context);
        }
    }

    public function notice($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::NOTICE)) {
            $this->logger->notice((string) $message, $context);
        }
    }

    public function info($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::INFO)) {
            $this->logger->info((string) $message, $context);
        }
    }

    public function debug($message, array $context = []): void
    {
        if ($this->shouldLog(LogLevel::DEBUG)) {
            $this->logger->debug((string) $message, $context);
        }
    }

    public function log($level, $message, array $context = []): void
    {
        // Do nothing
    }

    private function shouldLog(int $level): bool
    {
        return $level <= $this->logLevel;
    }
}
