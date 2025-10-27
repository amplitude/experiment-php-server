<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\EvaluationCore\Types\EvaluationVariant;
use AmplitudeExperiment\Http\HttpClientInterface;
use AmplitudeExperiment\Http\GuzzleHttpClient;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use const AmplitudeExperiment\VERSION;

require_once __DIR__ . '/../Version.php';

/**
 * Experiment client for fetching variants for a user remotely.
 * @category Core Usage
 */
class RemoteEvaluationClient
{
    private string $apiKey;
    private RemoteEvaluationConfig $config;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    /**
     * Creates a new RemoteEvaluationClient instance.
     *
     * @param string $apiKey The environment API Key
     * @param ?RemoteEvaluationConfig $config See {@link RemoteEvaluationConfig} for config options
     */
    public function __construct(string $apiKey, ?RemoteEvaluationConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? RemoteEvaluationConfig::builder()->build();
        $this->httpClient = $config->httpClient ?? $this->config->httpClient ?? new GuzzleHttpClient($this->config->guzzleClientConfig);
        $this->logger = new InternalLogger($this->config->logger ?? new DefaultLogger(), $this->config->logLevel);
    }

    private function fetchWithOptions(User $user, FetchOptions $options): array
    {
        $flagKeys = $options->flagKeys;
        $tracksAssignment = $options->tracksAssignment;
        $tracksExposure = $options->tracksExposure;

        if ($user->userId == null && $user->deviceId == null) {
            $this->logger->warning('[Experiment] user id and device id are null; Amplitude may not resolve identity');
        }
        $this->logger->debug('[Experiment] Fetching variants for user: ' . json_encode($user->toArray()));

        // Define the request data
        $libraryUser = $user->copyToBuilder()->library('experiment-php-server/' . VERSION)->build();
        $userJson = json_encode($libraryUser->toArray());
        if ($userJson === false) {
            $this->logger->error('[Experiment] Failed to fetch variants: ' . json_last_error_msg());
            return [];
        }
        $serializedUser = base64_encode($userJson);

        // Define the request URL
        $endpoint = $this->config->serverUrl . '/sdk/v2/vardata?v=0';
        $request = $this->httpClient->createRequest('GET', $endpoint)
            ->withHeader('Authorization', 'Api-Key ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Amp-Exp-User', $serializedUser);

        if ($flagKeys !== null && !empty($flagKeys)) {
            $flagKeysJson = json_encode($flagKeys);
            if ($flagKeysJson === false) {
                $this->logger->error('[Experiment] Failed to fetch variants: ' . json_last_error_msg());
                return [];
            }
            $request = $request->withHeader('X-Amp-Exp-Flag-Keys', base64_encode($flagKeysJson));
        }

        if ($tracksAssignment !== null) {
            $request = $request->withHeader('X-Amp-Exp-Track', $tracksAssignment ? 'track' : 'no-track');
        }
        if ($tracksExposure !== null) {
            $request = $request->withHeader('X-Amp-Exp-Exposure-Track', $tracksExposure ? 'track' : 'no-track');
        }

        $httpClient = $this->httpClient->getClient();

        try {
            $response = $httpClient->sendRequest($request);
            if ($response->getStatusCode() != 200) {
                $this->logger->error('[Experiment] Failed to fetch variants: ' . $response->getBody());
                return [];
            }

            $results = EvaluationVariant::fromEvaluationResults(json_decode($response->getBody(), true));
            $variants = array_map(function ($flagResult) {
                return Variant::convertEvaluationVariantToVariant($flagResult);
            }, $results);
            $this->logger->debug('[Experiment] Fetched variants: ' . $response->getBody());
            return $variants;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('[Experiment] Failed to fetch variants: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch all variants for a user.
     *
     * This method will automatically retry if configured (default).
     *
     * @param User $user The {@link User} context
     * @param mixed $arg Either flags to evaluate for this specific fetch request or a {@link FetchOptions} object.
     * If an array is provided, it is treated as the flag keys and will be converted to a {@link FetchOptions} object.
     * If a {@link FetchOptions} object is provided, it will be used as is.
     * If no arguments are provided, a default {@link FetchOptions} object without any options will be used.
     * @return array<Variant> A {@link Variant} array for the user on success, empty array on error.
     */
    public function fetch(User $user, mixed $arg = null): array
    {
        if ($arg !== null && is_array($arg)) {
            return $this->fetchWithOptions($user, new FetchOptions($arg, null, null));
        }
        if ($arg !== null && is_object($arg) && $arg instanceof FetchOptions) {
            return $this->fetchWithOptions($user, $arg);
        }
        return $this->fetchWithOptions($user, new FetchOptions());
    }
}
