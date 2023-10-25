<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\Local\LocalEvaluationConfig;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use function AmplitudeExperiment\initializeLogger;

include __DIR__ . '/../Utils.php';

const FLAG_CONFIG_TIMEOUT = 5000;

class FlagConfigFetcher
{
    private Logger $logger;
    private string $apiKey;
    private string $serverUrl;
    private Client $httpClient;

    public function __construct(string $apiKey, string $serverUrl = LocalEvaluationConfig::DEFAULTS["serverUrl"], bool $debug = false)
    {
        $this->apiKey = $apiKey;
        $this->serverUrl = $serverUrl;
        $this->httpClient = new Client();
        $this->logger = initializeLogger($debug);
    }

    // TODO add docs + check error thrown?
    public function fetch(): PromiseInterface
    {
        $endpoint = $this->serverUrl . '/sdk/flags';
        $headers = [
            'Authorization' => 'Api-Key ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json;charset=utf-8',
            'X-Amp-Exp-Library' => 'experiment-php-server/' . VERSION,
        ];
        $this->logger->debug('[Experiment] Get flag configs');
        $promise = $this->httpClient->requestAsync('GET', $endpoint, [
            'headers' => $headers,
            'timeout' => FLAG_CONFIG_TIMEOUT / 1000,
        ]);

        return $promise->then(
            function (ResponseInterface $response) {
                // Check if the HTTP status code is not 200
                if ($response->getStatusCode() !== 200) {
                    $errorMessage = 'flagConfigs - received error response: ' . $response->getStatusCode() . ': ' . $response->getBody();
                    throw new RuntimeException($errorMessage);
                }
                $this->logger->debug('[Experiment] Got flag configs: ' . $response->getBody());
                return $this->parse(json_decode($response->getBody(), true));
            },
            function (Exception $reason) {
                $this->logger->error('[Experiment] flagConfigs - received error response: ' . $reason->getMessage());
                throw $reason;
            }
        );
    }

    private function parse(string $flagConfigs): array
    {
        $flagConfigsArray = json_decode($flagConfigs, true);
        $flagConfigsRecord = [];
        if ($flagConfigsArray !== null) {
            foreach ($flagConfigsArray as $flagConfig) {
                $flagConfigsRecord[$flagConfig['flagKey']] = $flagConfig;
            }
        }
        return $flagConfigsRecord;
    }
}
