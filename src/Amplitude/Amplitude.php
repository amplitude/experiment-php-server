<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Backoff;
use AmplitudeExperiment\Http\HttpClientInterface;
use AmplitudeExperiment\Http\GuzzleHttpClient;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Promise\Utils;

/**
 * Amplitude client for sending events to Amplitude.
 */
class Amplitude
{
    private string $apiKey;
    /**
     * @var array<array<string,mixed>>
     */
    protected array $queue = [];
    protected HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private AmplitudeConfig $config;
    private Client $asyncHttpClient;
    private array $flushPromises = [];

    public function __construct(string $apiKey, LoggerInterface $logger, AmplitudeConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->config = $config ?? AmplitudeConfig::builder()->build();
        $this->httpClient = $this->config->httpClient ?? $this->config->httpClient ?? new GuzzleHttpClient($this->config->guzzleClientConfig);
        $this->asyncHttpClient = new Client();
        register_shutdown_function(array($this, 'stop'));
    }

    public function flush(): void
    {
        $payload = ["api_key" => $this->apiKey, "events" => $this->queue, "options" => ["min_id_length" => $this->config->minIdLength]];
        $this->post($this->config->serverUrl, $payload);
    }

    public function logEvent(Event $event): void
    {
        $this->queue[] = $event->toArray();
        if (count($this->queue) >= $this->config->flushQueueSize) {
            $this->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function logEventAsync(Event $event): void
    {
        $this->flushPromises[] = $this->postAsync($this->config->serverUrl, ["api_key" => $this->apiKey, "events" => [$event->toArray()], "options" => ["min_id_length" => $this->config->minIdLength]]);
    }

    /**
     * Flush the queue when the client is destructed.
     */
    public function stop()
    {
        if (count($this->queue) > 0) {
            $this->flush();
        }
        if (count($this->flushPromises) > 0) {
            Utils::all($this->flushPromises)->wait();
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function post(string $url, array $payload): void
    {
        $httpClient = $this->httpClient->getClient();
        $payloadJson = json_encode($payload);
        if ($payloadJson === false) {
            $this->logger->error('[Amplitude] Failed to encode payload: ' . json_last_error());
            return;
        }
        $request = $this->httpClient
            ->createRequest('POST', $url, $payloadJson)
            ->withHeader('Content-Type', 'application/json');
        try {
            $response = $httpClient->sendRequest($request);
            if ($response->getStatusCode() != 200) {
                $this->logger->error('[Amplitude] Failed to send event: ' . $payloadJson . ', ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
                return;
            }
            $this->logger->debug("[Amplitude] Event sent successfully: " . $payloadJson);
            $this->queue = [];

        } catch (ClientExceptionInterface $e) {
            $this->logger->error('[Amplitude] Failed to send event: ' . $payloadJson . ', ' . $e->getMessage());
        }
    }

    public function flushAsync(): PromiseInterface
    {
        $payload = ["api_key" => $this->apiKey, "events" => $this->queue, "options" => ["min_id_length" => $this->config->minIdLength]];

        // Fetch initial flag configs and await the result.
        return Backoff::doWithBackoff(
            function () use ($payload) {
                return $this->postAsync($this->config->serverUrl, $payload)->then(
                    function () {
                        $this->queue = [];
                    }
                );
            },
            new Backoff(5, 1, 1, 1)
        );
    }

    /**
     * @throws Exception
     */
    private function postAsync(string $url, array $payload): PromiseInterface
    {
        // Using sendAsync to make an asynchronous request
        $promise = $this->asyncHttpClient->postAsync($url, [
            'json' => $payload,
        ]);

        return $promise->then(
            function ($response) use ($payload) {
                // Process the successful response if needed
                $this->logger->debug("[Amplitude] Event sent successfully: " . json_encode($payload));
            },
            function (Exception $exception) use ($payload) {
                // Handle the exception for async request
                $this->logger->error('[Amplitude] Failed to send event: ' . json_encode($payload) . ', ' . $exception->getMessage());
                throw $exception;
            }
        );
    }
}
