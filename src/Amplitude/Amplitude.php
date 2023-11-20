<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Backoff;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Logger;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Util.php';

class Amplitude
{
    private string $apiKey;
    protected array $queue = [];
    protected Client $httpClient;
    private Logger $logger;
    private ?AmplitudeConfig $config;

    public function __construct(string $apiKey, bool $debug, AmplitudeConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client();
        $this->logger = initializeLogger($debug);
        $this->config = $config ?? AmplitudeConfig::builder()->build();
    }

    public function flush(): PromiseInterface
    {
        $payload = ["api_key" => $this->apiKey, "events" => $this->queue];

        // Fetch initial flag configs and await the result.
        return Backoff::doWithBackoff(
            function () use ($payload) {
                return $this->post($this->config->serverUrl, $payload)->then(
                    function () {
                        $this->queue = [];
                    }
                );
            },
            new Backoff($this->config->flushMaxRetries, 1, 1, 1)
        );
    }

    public function logEvent(Event $event)
    {
        $this->queue[] = $event->toArray();
        if (count($this->queue) >= $this->config->flushQueueSize) {
            $this->flush()->wait();
        }
    }

    public function __destruct()
    {
        if (count($this->queue) > 0) {
            $this->flush()->wait();
        }
    }

    private function post(string $url, array $payload): PromiseInterface
    {
        // Using sendAsync to make an asynchronous request
        $promise = $this->httpClient->postAsync($url, [
            'json' => $payload,
        ]);

        return $promise->then(
            function ($response) use ($payload) {
                // Process the successful response if needed
                $this->logger->debug("[Amplitude] Event sent successfully: " . json_encode($payload));
            },
            function (\Exception $exception) use ($payload) {
                // Handle the exception for async request
                $this->logger->error('[Amplitude] Failed to send event: ' . json_encode($payload) . ', ' . $exception->getMessage());
                throw $exception;
            }
        );
    }
}
