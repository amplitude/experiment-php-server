<?php

namespace AmplitudeExperiment;

use Exception;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

class Backoff {
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

    public static function doWithBackoff(callable $action, Backoff $backoffPolicy) : PromiseInterface {
        $delay = $backoffPolicy->min;
        for ($i = 0; $i < $backoffPolicy->attempts; $i++) {
            try {
                return $action();
            } catch (Exception $e) {
                usleep($delay * 1000);
                $delay = min($delay * $backoffPolicy->scalar, $backoffPolicy->max);
            }
        }
        return Create::promiseFor(null);
    }
}
