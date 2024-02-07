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
    private LoggerInterface $logger;
    private AmplitudeConfig $config;
    private Client $asyncHttpClient;
    private array $flushPromises = [];

    public function __construct(string $apiKey, LoggerInterface $logger, AmplitudeConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->config = $config ?? AmplitudeConfig::builder()->build();
        $this->asyncHttpClient = new Client();
        register_shutdown_function(array($this, 'stop'));
    }

    public function logEvent(Event $event): void
    {
        $this->flushPromises[] = $this->flush($event);
    }

    /**
     * Flush the queue at the end of a process.
     */
    public function stop()
    {
        if (count($this->flushPromises) > 0) {
            Utils::all($this->flushPromises)->wait();
            $this->flushPromises = [];
        }
    }

    public function flush(Event $event): PromiseInterface
    {
        $payload = ["api_key" => $this->apiKey, "events" => [$event->toArray()], "options" => ["min_id_length" => $this->config->minIdLength]];

        // Fetch initial flag configs and await the result.
        return Backoff::doWithBackoff(
            function () use ($payload) {
                return $this->post($this->config->serverUrl, $payload);
            },
            new Backoff(5, 1, 1, 1)
        );
    }

    private function post(string $url, array $payload): PromiseInterface
    {
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
