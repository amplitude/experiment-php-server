<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Util;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Version.php';
require_once __DIR__ . '/../Util.php';

const FLAG_CONFIG_TIMEOUT = 5000;

class FlagConfigFetcher
{
    private Logger $logger;
    private string $apiKey;
    private string $serverUrl;
    private Client $httpClient;

    public function __construct(string $apiKey, bool $debug, string $serverUrl = LocalEvaluationConfig::DEFAULTS["serverUrl"])
    {
        $this->apiKey = $apiKey;
        $this->serverUrl = $serverUrl;
        $this->httpClient = new Client();
        $this->logger = initializeLogger($debug);
    }

    /**
     * Fetch local evaluation mode flag configs from the Experiment API server.
     * These flag configs can be used to perform local evaluation.
     *
     * @return PromiseInterface
     */
    public function fetch(): PromiseInterface
    {
        $endpoint = $this->serverUrl . '/sdk/v2/flags';
        $headers = [
            'Authorization' => 'Api-Key ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json;charset=utf-8',
            'X-Amp-Exp-Library' => 'experiment-php-server/' . VERSION,
        ];
        $this->logger->debug('[Experiment] Fetch flag configs');
        $promise = $this->httpClient->requestAsync('GET', $endpoint, [
            'headers' => $headers,
            'timeout' => FLAG_CONFIG_TIMEOUT / 1000,
        ]);

        return $promise->then(
            function (ResponseInterface $response) {
                // Check if the HTTP status code is not 200
                if ($response->getStatusCode() !== 200) {
                    $errorMessage = '[Experiment] Fetch flag configs - received error response: ' . $response->getStatusCode() . ': ' . $response->getBody();
                    throw new RuntimeException($errorMessage);
                }
                $this->logger->debug('[Experiment] Got flag configs: ' . $response->getBody());
                return $this->parse(json_decode($response->getBody(), true));
            },
            function (Exception $reason) {
                $this->logger->error('[Experiment] Fetch flag configs - received error response: ' . $reason->getMessage());
                throw $reason;
            }
        );
    }

    private function parse(array $flagConfigs): array
    {
        $flagConfigsRecord = [];
        if ($flagConfigs) {
            foreach ($flagConfigs as $flagConfig) {
                $flagConfigsRecord[$flagConfig['key']] = $flagConfig;
            }
        }
        return $flagConfigsRecord;
    }
}
