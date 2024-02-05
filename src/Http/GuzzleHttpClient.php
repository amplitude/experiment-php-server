<?php

namespace AmplitudeExperiment\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;

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

/**
 * A default {@link HttpClientInterface} implementation that uses Guzzle.
 */
class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;
    /**
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $handlerStack = HandlerStack::create();
        $this->config = array_merge(GUZZLE_DEFAULTS, $config);

        // Add middleware for retries
        $handlerStack->push(Middleware::retry(
            function ($retries, Request $request, $response = null, $exception = null) {
                // Retry if the maximum number of retries is not reached and an exception occurred
                return $retries < $this->config['retries'] && $exception instanceof Exception;
            },
            function ($retries) {
                // Calculate delay
                return $this->calculateDelayMillis($retries);
            }
        ));

        // Create a Guzzle client with the custom handler stack
        $this->client = new Client(['handler' => $handlerStack, RequestOptions::TIMEOUT => $this->config['timeoutMillis'] / 1000]);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function createRequest(string $method, string $uri, ?string $body = null): Request
    {
        return new Request($method, $uri, [], $body);
    }


    protected function calculateDelayMillis(int $iteration): int
    {
        $delayMillis = $this->config['retryBackoffMinMillis'];

        for ($i = 1; $i < $iteration; $i++) {
            $delayMillis = min(
                $delayMillis * $this->config['retryBackoffScalar'],
                $this->config['retryBackoffMaxMillis']
            );
        }
        return $delayMillis;
    }
}
