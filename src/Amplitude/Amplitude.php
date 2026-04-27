<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Http\HttpClientFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Amplitude client for sending events to Amplitude.
 */
class Amplitude
{
    /**
     * The Amplitude Project API key.
     */
    private string $apiKey;
    /**
     * @var array<array<string,mixed>>
     */
    protected array $queue = [];
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    private LoggerInterface $logger;
    private AmplitudeConfig $config;

    public function __construct(string $apiKey, ?AmplitudeConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? AmplitudeConfig::builder()->build();
        $this->logger = $this->config->logger ?? new NullLogger();
        $this->httpClient = HttpClientFactory::resolveClient($this->config->httpClient, $this->config->retryConfig);
        $this->requestFactory = HttpClientFactory::resolveRequestFactory($this->config->requestFactory);
        $this->streamFactory = HttpClientFactory::resolveStreamFactory(null);
    }

    public function flush(): void
    {
        $payload = [
            "api_key" => $this->apiKey,
            "events" => $this->queue,
            "options" => ["min_id_length" => $this->config->minIdLength]
        ];

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
     * Flush the queue when the client is destructed.
     */
    public function __destruct()
    {
        if (count($this->queue) > 0) {
            $this->flush();
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function post(string $url, array $payload): void
    {
        $payloadJson = json_encode($payload);

        if ($payloadJson === false) {
            $this->logger->error('[Amplitude] Failed to encode payload: ' . json_last_error());
            return;
        }

        $payloadString = $this->payloadToString($payload);

        $request = $this->requestFactory->createRequest('POST', $url)
            ->withBody($this->streamFactory->createStream($payloadJson))
            ->withHeader('Content-Type', 'application/json');

        try {
            $response = $this->httpClient->sendRequest($request);
            if ($response->getStatusCode() != 200) {
                $this->logger->error('[Amplitude] Failed to send event: ' . $payloadString . ', ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
                return;
            }
            $this->logger->debug("[Amplitude] Event sent successfully: " . $payloadString);
            $this->queue = [];

        } catch (ClientExceptionInterface $e) {
            $this->logger->error('[Amplitude] Failed to send event: ' . $payloadString . ', ' . $e->getMessage());
        }
    }

    /**
     * Convert the payload to a string for logging
     *
     * @param array<string,mixed> $payload
     * @return string
     */
    private function payloadToString(array $payload): string
    {
        unset($payload['api_key']);
        return json_encode($payload) ?: '{}';
    }
}
