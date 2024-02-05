<?php

namespace AmplitudeExperiment\Logger;

class LogLevel
{
    public const NO_LOG = -1;
    public const EMERGENCY = 0;
    public const ALERT = 1;
    public const CRITICAL = 2;
    public const ERROR = 3;
    public const WARNING = 4;
    public const NOTICE = 5;
    public const INFO = 6;
    public const DEBUG = 7;

    public static function toString(int $level): string
    {
        switch ($level) {
            case self::DEBUG:
                return 'DEBUG';
            case self::INFO:
                return 'INFO';
            case self::NOTICE:
                return 'NOTICE';
            case self::WARNING:
                return 'WARNING';
            case self::ERROR:
                return 'ERROR';
            case self::CRITICAL:
                return 'CRITICAL';
            case self::ALERT:
                return 'ALERT';
            case self::EMERGENCY:
                return 'EMERGENCY';
            default:
                return '';
        }
    }
}
