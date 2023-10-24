<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\FetchOptions;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Utils;
use AmplitudeExperiment\Variant;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Throwable;

include __DIR__ . '/../Version.php';

/**
 * Experiment client for fetching variants for a user remotely.
 * @category Core Usage
 */
class RemoteEvaluationClient
{
    private string $apiKey;
    private RemoteEvaluationConfig $config;
    private Client $httpClient;
    private Logger $logger;

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
        $this->httpClient = new Client();
        $this->logger = Utils::initializeLogger($this->config->debug ? Logger::DEBUG : Logger::INFO);
    }

    /**
     * Fetch all variants for a user.
     *
     * This method will automatically retry if configured (default).
     *
     * @param $user User The {@link User} context
     * @param $options ?FetchOptions The {@link FetchOptions} for this specific fetch request.
     * @return PromiseInterface A {@link Variant} array for the user on success, empty array on error.
     * @throws Exception
     */
    public function fetch(User $user, ?FetchOptions $options = null): PromiseInterface
    {
        if ($user->userId == null && $user->deviceId == null) {
            $this->logger->warning('[Experiment] user id and device id are null; Amplitude may not resolve identity');
        }
        $this->logger->debug('[Experiment] Fetching variants for user: ' . json_encode($user));

        return $this->doFetch($user, $this->config->fetchTimeoutMillis, $options)
            ->otherwise(function (Throwable $e) use ($user, $options) {
                // Handle the exception
                $this->logger->error('[Experiment] Fetch failed: ' . $e->getMessage());

                // Retry the fetch
                return $this->retryFetch($user, $options)
                    ->then(function ($result) {
                        // Process the result if retry is successful
                        return $result;
                    })
                    ->otherwise(function (Throwable $retryException) use ($e) {
                        // Handle the exception for the retry attempt
                        $this->logger->error('[Experiment] Retry failed: ' . $retryException->getMessage());

                        // Re-throw the original exception if needed
                        throw $e;
                    });
            });
    }

    public function doFetch(User $user, int $timeoutMillis, ?FetchOptions $options = null): PromiseInterface
    {
        // Define the request data
        $libraryUser = $user->copyToBuilder()->library('experiment-php-server/' . VERSION)->build();
        $serializedUser = base64_encode(json_encode($libraryUser));

        // Define the request URL
        $endpoint = $this->config->serverUrl . '/sdk/vardata';

        // Define the request headers
        $headers = [
            'Authorization' => 'Api-Key ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'X-Amp-Exp-User' => $serializedUser,
        ];

        if ($options && $options->flagKeys) {
            $headers['X-Amp-Exp-Flag-Keys'] = base64_encode(json_encode($options->flagKeys));
        }

        $promise = $this->httpClient->requestAsync('GET', $endpoint, [
            'headers' => $headers,
            'timeout' => $timeoutMillis / 1000,
        ]);

        return $promise->then(
            function (ResponseInterface $response) {
                $variants = $this->parseRemoteResponse(json_decode($response->getBody(), true));
                $this->logger->debug('[Experiment] Fetched variants: ' . $response->getBody());
                return $variants;
            },
            function (Exception $reason) {
                $this->logger->error('[Experiment] Failed to fetch variants: ' . $reason->getMessage());
                throw $reason;
            }
        );
    }

    /**
     * @throws Exception
     */
    private function retryFetch(User $user, ?FetchOptions $options = null): PromiseInterface
    {
        if ($this->config->fetchRetries == 0) {
            $promise = new Promise();
            $promise->resolve([]);
            return $promise;
        }

        $this->logger->debug('[Experiment] Retrying fetch');

        $err = null;
        $delayMillis = $this->config->fetchRetryBackoffMinMillis;

        for ($i = 0; $i < $this->config->fetchRetries; $i++) {
            usleep($delayMillis * 1000); // Convert to microseconds

            try {
                return $this->doFetch(
                    $user,
                    $this->config->fetchRetryTimeoutMillis,
                    $options
                )->then(
                    function ($result) {
                        return $result;
                    },
                    function ($e) use (&$err) {
                        $this->logger->error('[Experiment] Retry failed: ' . $e->getMessage());
                        $err = $e;
                    }
                );
            } catch (Exception $e) {
                $this->logger->error('[Experiment] Retry failed: ' . $e->getMessage());
                $err = $e;
            }

            $delayMillis = min(
                $delayMillis * $this->config->fetchRetryBackoffScalar,
                $this->config->fetchRetryBackoffMaxMillis
            );
        }

        throw $err;
    }

    private function parseRemoteResponse(array $responseData): PromiseInterface
    {
        $variants = [];
        foreach ($responseData as $key => $data) {
            $value = $data['value'] ?? null;
            if ($value == null) {
                $value = $data['key'] ?? null;
            }
            $variant = new Variant($value, $data['payload'] ?? null);
            $variants[$key] = $variant;
        }
        $promise = new Promise();
        $promise->resolve($variants);
        return $promise;
    }
}