<?php

namespace AmplitudeExperiment\Http;

use InvalidArgumentException;

/**
 * Configures {@link RetryingClient} retry behavior. Defaults preserve v1
 * SDK semantics: 9 total attempts (1 + 8 retries) with exponential backoff
 * scaling from 500ms to a 10s cap, applied to GET requests only.
 *
 * Retries trigger only on PSR-18 transport exceptions
 * ({@link \Psr\Http\Client\ClientExceptionInterface}). 4xx and 5xx
 * responses are not retried.
 */
class RetryConfig
{
    public int $attempts;
    public int $backoffMinMillis;
    public int $backoffMaxMillis;
    public float $backoffScalar;
    /** @var string[] Uppercase HTTP method names eligible for retry. */
    public array $retryMethods;

    /**
     * @param string[] $retryMethods HTTP methods eligible for retry. Methods
     *  not in this list pass through to the underlying client without retry.
     *  Default ['GET'] avoids amplifying duplicate-event risk on
     *  non-idempotent POSTs; opt POST in explicitly when retrying it is safe
     *  for the call site.
     */
    public function __construct(
        int $attempts = 9,
        int $backoffMinMillis = 500,
        int $backoffMaxMillis = 10000,
        float $backoffScalar = 1.5,
        array $retryMethods = ['GET']
    ) {
        if ($attempts < 1) {
            throw new InvalidArgumentException('attempts must be >= 1');
        }
        if ($backoffMinMillis < 0 || $backoffMaxMillis < $backoffMinMillis) {
            throw new InvalidArgumentException('backoff bounds invalid');
        }
        if ($backoffScalar < 1.0) {
            throw new InvalidArgumentException('backoffScalar must be >= 1.0');
        }
        $this->attempts = $attempts;
        $this->backoffMinMillis = $backoffMinMillis;
        $this->backoffMaxMillis = $backoffMaxMillis;
        $this->backoffScalar = $backoffScalar;
        $this->retryMethods = array_values(array_map('strtoupper', $retryMethods));
    }
}
