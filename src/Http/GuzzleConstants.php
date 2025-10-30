<?php

namespace AmplitudeExperiment\Http;

const GUZZLE_DEFAULTS = [
    /**
     * The request socket timeout, in milliseconds.
     */
    'timeoutMillis' => 10000,
    /**
     * The number of retries to attempt before failing
     */
    'retries' => 8,
    /**
     * Retry backoff minimum (starting backoff delay) in milliseconds. The minimum backoff is scaled by
     * `retryBackoffScalar` after each retry failure.
     */
    'retryBackoffMinMillis' => 500,
    /**
     * Retry backoff maximum in milliseconds. If the scaled backoff is greater than the max, the max is
     * used for all subsequent retries.
     */
    'retryBackoffMaxMillis' => 10000,
    /**
     * Scales the minimum backoff exponentially.
     */
    'retryBackoffScalar' => 1.5,
    /**
     * The request timeout for retrying fetch requests.
     */
    'retryTimeoutMillis' => 10000
];
