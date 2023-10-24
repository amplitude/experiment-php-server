<?php

namespace AmplitudeExperiment\Remote;

/**
 * Configuration options. This is an object that can be created using
 * a {@link RemoteEvaluationConfigBuilder}. Example usage:
 *
 *`RemoteEvaluationConfig::builder()->serverUrl("https://api.lab.amplitude.com/")->build()`
 */
class RemoteEvaluationConfig
{
    /**
     * Set to true to log some extra information to the console.
     */
    public bool $debug;
    /**
     * The server endpoint from which to request variants.
     */
    public string $serverUrl;
    /**
     * The request socket timeout, in milliseconds.
     */
    public int $fetchTimeoutMillis;
    /**
     * The number of retries to attempt before failing
     */
    public int $fetchRetries;
    /**
     * Retry backoff minimum (starting backoff delay) in milliseconds. The minimum backoff is scaled by
     * `fetchRetryBackoffScalar` after each retry failure.
     */
    public int $fetchRetryBackoffMinMillis;
    /**
     * Retry backoff maximum in milliseconds. If the scaled backoff is greater than the max, the max is
     * used for all subsequent retries.
     */
    public int $fetchRetryBackoffMaxMillis;
    /**
     * Scales the minimum backoff exponentially.
     */
    public float $fetchRetryBackoffScalar;
    /**
     * The request timeout for retrying fetch requests.
     */
    public ?int $fetchRetryTimeoutMillis;

    public function __construct(
        bool   $debug,
        string $serverUrl,
        int    $fetchTimeoutMillis,
        int    $fetchRetries,
        int    $fetchRetryBackoffMinMillis,
        int    $fetchRetryBackoffMaxMillis,
        float  $fetchRetryBackoffScalar,
        int    $fetchRetryTimeoutMillis
    )
    {
        $this->debug = $debug;
        $this->serverUrl = $serverUrl;
        $this->fetchTimeoutMillis = $fetchTimeoutMillis;
        $this->fetchRetries = $fetchRetries;
        $this->fetchRetryBackoffMinMillis = $fetchRetryBackoffMinMillis;
        $this->fetchRetryBackoffMaxMillis = $fetchRetryBackoffMaxMillis;
        $this->fetchRetryBackoffScalar = $fetchRetryBackoffScalar;
        $this->fetchRetryTimeoutMillis = $fetchRetryTimeoutMillis;
    }

    public static function builder(): RemoteEvaluationConfigBuilder
    {
        return new RemoteEvaluationConfigBuilder();
    }
}
