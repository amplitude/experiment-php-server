<?php

namespace AmplitudeExperiment\Logger;

class LogLevel
{
    public const EMERGENCY = 8;
    public const ALERT = 7;
    public const CRITICAL = 6;
    public const ERROR = 5;
    public const WARNING = 4;
    public const NOTICE = 3;
    public const INFO = 2;
    public const DEBUG = 1;

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
