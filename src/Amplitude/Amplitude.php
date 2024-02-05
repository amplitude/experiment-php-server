<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Http\HttpClientInterface;
use AmplitudeExperiment\Http\GuzzleHttpClient;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

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

    public function __construct(string $apiKey, LoggerInterface $logger, AmplitudeConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->config = $config ?? AmplitudeConfig::builder()->build();
        $this->httpClient = $this->config->httpClient ?? $this->config->httpClient ?? new GuzzleHttpClient($this->config->guzzleClientConfig);
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
}
