<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\Http\FetchClientInterface;
use AmplitudeExperiment\Http\GuzzleFetchClient;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../Version.php';
require_once __DIR__ . '/../Util.php';

/**
 * Experiment client for fetching variants for a user remotely.
 * @category Core Usage
 */
class RemoteEvaluationClient
{
    private string $apiKey;
    private RemoteEvaluationConfig $config;
    private FetchClientInterface $httpClient;
    private LoggerInterface $logger;

    /**
     * Creates a new RemoteEvaluationClient instance.
     *
     * @param $apiKey string The environment API Key
     * @param $config ?RemoteEvaluationConfig See {@link RemoteEvaluationConfig} for config options
     */
    public function __construct(string $apiKey, ?RemoteEvaluationConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? RemoteEvaluationConfig::builder()->build();
        $this->httpClient = $config->fetchClient ?? $this->config->fetchClient ?? new GuzzleFetchClient($this->config->guzzleClientConfig);
        $this->logger = new InternalLogger($this->config->logger ?? new DefaultLogger(), $this->config->logLevel);
    }

    /**
     * Fetch all variants for a user.
     *
     * This method will automatically retry if configured (default).
     *
     * @param $user User The {@link User} context
     * @param $flagKeys array The flags to evaluate for this specific fetch request.
     * @return array A {@link Variant} array for the user on success, empty array on error. d
     */
    public function fetch(User $user, array $flagKeys = []): array
    {
        if ($user->userId == null && $user->deviceId == null) {
            $this->logger->warning('[Experiment] user id and device id are null; Amplitude may not resolve identity');
        }
        $this->logger->debug('[Experiment] Fetching variants for user: ' . json_encode($user->toArray()));

        // Define the request data
        $libraryUser = $user->copyToBuilder()->library('experiment-php-server/' . VERSION)->build();
        $serializedUser = base64_encode(json_encode($libraryUser->toArray()));

        // Define the request URL
        $endpoint = $this->config->serverUrl . '/sdk/v2/vardata?v=0';
        $request = $this->httpClient->createRequest('GET', $endpoint)
            ->withHeader('Authorization', 'Api-Key ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Amp-Exp-User', $serializedUser);

        if (!empty($flagKeys)) {
            $request = $request->withHeader('X-Amp-Exp-Flag-Keys', base64_encode(json_encode($flagKeys)));
        }

        $fetchClient = $this->httpClient->getClient();

        try {
            $response = $fetchClient->sendRequest($request);
            if ($response->getStatusCode() != 200) {
                $this->logger->error('[Experiment] Failed to fetch variants: ' . $response->getBody());
                return [];
            }

            $results = json_decode($response->getBody(), true);
            $variants = [];
            foreach ($results as $flagKey => $flagResult) {
                $variants[$flagKey] = Variant::convertEvaluationVariantToVariant($flagResult);
            }
            $this->logger->debug('[Experiment] Fetched variants: ' . $response->getBody());
            return $variants;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('[Experiment] Failed to fetch variants: ' . $e->getMessage());
            return [];
        }
    }
}
