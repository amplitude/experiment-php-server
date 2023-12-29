<?php

namespace AmplitudeExperiment\Http;

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
    'fetchTimeoutMillis' => 10000,
    /**
     * The number of retries to attempt before failing
     */
    'fetchRetries' => 8,
    /**
     * Retry backoff minimum (starting backoff delay) in milliseconds. The minimum backoff is scaled by
     * `fetchRetryBackoffScalar` after each retry failure.
     */
    'fetchRetryBackoffMinMillis' => 500,
    /**
     * Retry backoff maximum in milliseconds. If the scaled backoff is greater than the max, the max is
     * used for all subsequent retries.
     */
    'fetchRetryBackoffMaxMillis' => 10000,
    /**
     * Scales the minimum backoff exponentially.
     */
    'fetchRetryBackoffScalar' => 1.5,
    /**
     * The request timeout for retrying fetch requests.
     */
    'fetchRetryTimeoutMillis' => 10000
];

/**
 * A default FetchClientInterface implementation that uses Guzzle.
 */
class GuzzleFetchClient implements FetchClientInterface
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
                return $retries < $this->config['fetchRetries'] && $exception instanceof \Exception;
            },
            function ($retries) {
                // Calculate delay
                return $this->calculateDelayMillis($retries);
            }
        ));

        // Create a Guzzle client with the custom handler stack
        $this->client = new Client(['handler' => $handlerStack, RequestOptions::TIMEOUT => $this->config['fetchTimeoutMillis'] / 1000]);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function createRequest(string $method, string $uri): Request
    {
        return new Request($method, $uri);
    }


    protected function calculateDelayMillis(int $iteration): int
    {
        $delayMillis = $this->config['fetchRetryBackoffMinMillis'];

        for ($i = 0; $i < $iteration; $i++) {
            $delayMillis = min(
                $delayMillis * $this->config['fetchRetryBackoffScalar'],
                $this->config['fetchRetryBackoffMaxMillis']
            );
        }
        return $delayMillis;
    }
}
