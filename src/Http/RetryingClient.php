<?php

namespace AmplitudeExperiment\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 decorator that retries the wrapped client on transport failures.
 *
 * Retry is method-gated via {@link RetryConfig::$retryMethods}. Requests
 * whose method is not eligible pass through to the inner client unchanged
 * — not even one retry — so a user-supplied client that already handles
 * its own retry semantics is not double-wrapped.
 *
 * Only PSR-18 {@link ClientExceptionInterface} triggers retry. Successful
 * responses (including 4xx/5xx) are returned immediately. Other throwables
 * propagate without retry.
 */
class RetryingClient implements ClientInterface
{
    private ClientInterface $inner;
    private RetryConfig $config;

    public function __construct(ClientInterface $inner, ?RetryConfig $config = null)
    {
        $this->inner = $inner;
        $this->config = $config ?? new RetryConfig();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (!in_array(strtoupper($request->getMethod()), $this->config->retryMethods, true)) {
            return $this->inner->sendRequest($request);
        }

        $delayMillis = $this->config->backoffMinMillis;

        for ($attempt = 1; $attempt < $this->config->attempts; $attempt++) {
            try {
                return $this->inner->sendRequest($request);
            } catch (ClientExceptionInterface $e) {
                usleep($delayMillis * 1000);
                $delayMillis = (int) min(
                    $delayMillis * $this->config->backoffScalar,
                    $this->config->backoffMaxMillis
                );
            }
        }

        return $this->inner->sendRequest($request);
    }
}
