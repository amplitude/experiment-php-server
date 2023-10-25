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
