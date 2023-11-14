<?php

namespace AmplitudeExperiment;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

function initializeLogger(?bool $debug): Logger
{
    $logger = new Logger('AmplitudeExperiment');
    $handler = new StreamHandler('php://stdout', $debug ? Logger::DEBUG : Logger::INFO);
    $formatter = new LineFormatter(null, null, false, true);
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);
    return $logger;
}

function hashCode(string $s): int
{
    $hash = 0;
    if (strlen($s) === 0) {
        return $hash;
    }
    for ($i = 0; $i < strlen($s); $i++) {
        $chr = ord($s[$i]);
        $hash = ($hash << 5) - $hash + $chr;
        $hash |= 0;
    }
    return $hash;
}
