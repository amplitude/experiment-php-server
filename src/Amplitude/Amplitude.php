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
    private array $queue = [];
    private Client $httpClient;
    private Logger $logger;

    public function __construct(string $apiKey, bool $debug = false)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client();
        $this->logger = initializeLogger($debug);
    }

    /**
     * @throws GuzzleException
     */
    public function flush(): PromiseInterface
    {
        $payload = ["api_key" => $this->apiKey, "events" => $this->queue];

        // Fetch initial flag configs and await the result.
        return Backoff::doWithBackoff(
            function () use ($payload) {
                return $this->post('https://api2.amplitude.com/batch', $payload)->then(
                    function () {
                        $this->queue = [];
                    }
                );
            },
            new Backoff(5, 1, 1, 1)
        );
    }

    public function logEvent(Event $event)
    {
        $this->queue[] = $event->toArray();
    }

    /**
     * @throws GuzzleException
     */
    public function __destruct()
    {
        if (count($this->queue) > 0) {
            $this->flush()->wait();
        }
    }

//    private function handleResponse(int $code): bool
//    {
//        if ($code >= 200 && $code < 300) {
//            $this->logger->debug("[Experiment] Event sent successfully");
//            return true;
//        } elseif ($code == 429) {
//            $this->logger->error("[Experiment] Event could not be sent - Exceeded daily quota");
//        } elseif ($code == 413) {
//            $this->logger->error("[Experiment] Event could not be sent - Payload too large");
//        } elseif ($code == 408) {
//            $this->logger->error("[Experiment] Event could not be sent - Timed out");
//        } elseif ($code >= 400 && $code < 500) {
//            $this->logger->error("[Experiment] Event could not be sent - Invalid request");
//        } elseif ($code >= 500) {
//            $this->logger->error("[Experiment] Event could not be sent - Http request failed");
//        } else {
//            $this->logger->error("[Experiment] Event could not be sent - Http request status unknown");
//        }
//        return false;
//    }

    /**
     * @throws GuzzleException
     */
    private function post(string $url, array $payload): PromiseInterface
    {
        // Using sendAsync to make an asynchronous request
        $promise = $this->httpClient->postAsync($url, [
            'json' => $payload,
        ]);

        return $promise->then(
            function ($response) {
                // Process the successful response if needed
                $this->logger->debug("[Amplitude] Event sent successfully");
                $responseData = json_decode($response->getBody(), true);

                return $responseData;
            },
            function (\Exception $exception) use ($payload) {
                // Handle the exception for async request
                $this->logger->error('[Amplitude] Failed to send event: ' . json_encode($payload) . ', ' . $exception->getMessage());
                throw $exception;
            }
        );
    }
}
