<?php

namespace AmplitudeExperiment\Test\Util;

use AmplitudeExperiment\Http\HttpClientInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;
use const AmplitudeExperiment\Http\GUZZLE_DEFAULTS;

class MockGuzzleHttpClient implements HttpClientInterface
{
    private Client $client;
    private array $config;

    public function __construct(array $config, HandlerStack $handlerStack)
    {
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
        return new Request($method, $uri);
    }

    protected function calculateDelayMillis($iteration): int
    {
        $delayMillis = $this->config['retryBackoffMinMillis'];

        for ($i = 0; $i < $iteration; $i++) {
            $delayMillis = min(
                $delayMillis * $this->config['retryBackoffScalar'],
                $this->config['retryBackoffMaxMillis']
            );
        }
        return $delayMillis;
    }
}
