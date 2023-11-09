<?php

namespace AmplitudeExperiment;

use Exception;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

class Backoff
{
    public int $attempts;
    public int $min;
    public int $max;
    public int $scalar;

    public function __construct(int $attempts, int $min, int $max, int $scalar)
    {
        $this->attempts = $attempts;
        $this->min = $min;
        $this->max = $max;
        $this->scalar = $scalar;
    }

    public static function doWithBackoff(callable $action, Backoff $backoffPolicy): PromiseInterface
    {
        $delay = $backoffPolicy->min;

        // Define a recursive function to handle retries
        $retry = function ($attempt) use ($delay, $action, $backoffPolicy, &$retry) {
            return $action()->then(
            // Success case
                function ($result) {
                    return Create::promiseFor($result);
                },
                // Failure case
                function () use ($attempt, $backoffPolicy, $retry, &$delay) {
                    if ($attempt < $backoffPolicy->attempts - 1) {
                        // Retry with backoff
                        usleep($delay * 1000);
                        $delay = min($delay * $backoffPolicy->scalar, $backoffPolicy->max);
                        return $retry($attempt + 1);
                    } else {
                        // All attempts failed
                        return Create::promiseFor(null);
                    }
                }
            );
        };

        // Start the first attempt
        return $retry(0);
    }
}
