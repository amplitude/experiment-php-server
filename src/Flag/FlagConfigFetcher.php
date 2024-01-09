<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\Http\HttpClientInterface;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../Version.php';

class FlagConfigFetcher
{
    private LoggerInterface $logger;
    private string $apiKey;
    private string $serverUrl;
    private HttpClientInterface $httpClient;

    public function __construct(string $apiKey, LoggerInterface $logger, HttpClientInterface $httpClient, string $serverUrl = LocalEvaluationConfig::DEFAULTS["serverUrl"])
    {
        $this->apiKey = $apiKey;
        $this->serverUrl = $serverUrl;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
     * Fetch local evaluation mode flag configs from the Experiment API server.
     * These flag configs can be used to perform local evaluation.
     *
     * @return array<array<mixed>> The flag configs
     * @throws ClientExceptionInterface
     */
    public function fetch(): array
    {
        $endpoint = $this->serverUrl . '/sdk/v2/flags?v=0';
        $request = $this->httpClient->createRequest('GET', $endpoint)
            ->withHeader('Authorization', 'Api-Key ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Amp-Exp-Library', 'experiment-php-server/' . VERSION);
        $this->logger->debug('[Experiment] Fetch flag configs');

        $httpClient = $this->httpClient->getClient();

        $response = $httpClient->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('[Experiment] Fetch flag configs - received error response: ' . $response->getStatusCode() . ': ' . $response->getBody());
            return [];
        }
        $this->logger->debug('[Experiment] Got flag configs: ' . $response->getBody());
        return $this->parse(json_decode($response->getBody(), true));

    }

    /**
     * @param array<array<mixed>> $flagConfigs
     * @return array<array<mixed>>
     */
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
