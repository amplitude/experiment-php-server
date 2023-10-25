<?php

namespace AmplitudeExperiment;

use Exception;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

class BackoffPolicy {
    public int $attempts;
    public int $min;
    public int $max;
    public int $scalar;

    public function __construct(int $attempts, int $min, int $max, int $scalar) {
        $this->attempts = $attempts;
        $this->min = $min;
        $this->max = $max;
        $this->scalar = $scalar;
    }
}

function doWithBackoff(callable $action, BackoffPolicy $backoffPolicy) : PromiseInterface {
    $delay = $backoffPolicy->min;
    for ($i = 0; $i < $backoffPolicy->attempts; $i++) {
        try {
            return $action();
        } catch (Exception $e) {
            usleep($delay * 1000);
            $delay = min($delay * $backoffPolicy->scalar, $backoffPolicy->max);
        }
    }
    return new Promise();
}

