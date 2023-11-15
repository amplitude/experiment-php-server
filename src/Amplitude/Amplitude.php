<?php

namespace AmplitudeExperiment\Amplitude;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
    public function flush()
    {
        $payload = ["api_key" => $this->apiKey, "events" => $this->queue];
        $this->post('https://api2.amplitude.com/batch', $payload);
        echo print_r($payload, true);
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
        $this->flush();
    }

    /**
     * @throws GuzzleException
     */
    private function post(string $url, array $payload)
    {
        // Using sendAsync to make an asynchronous request
        $promise = $this->httpClient->postAsync($url, [
            'json' => $payload,
        ]);

        return $promise->then(
            function ($response) {
                // Process the successful response if needed
                $statusCode = $response->getStatusCode();
                $responseData = json_decode($response->getBody(), true);
                // ... process the response data

                return $responseData;
            },
            function (\Exception $exception) {
                // Handle the exception for async request
                throw new \Exception("Async request error: " . $exception->getMessage());
            }
        );
    }
}
