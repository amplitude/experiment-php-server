<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Exposure\ExposureConfig;
use AmplitudeExperiment\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Configuration options. This is an object that can be created using
 * a {@link LocalEvaluationConfigBuilder}. Example usage:
 *
 *```
 * LocalEvaluationConfig::builder()->serverUrl("https://api.lab.amplitude.com/")->build();
 * ```
 */
class LocalEvaluationConfig
{
    /**
     * Set to use a custom PSR-3 logger. If not set, a {@link \Psr\Log\NullLogger} is used
     * and SDK log messages are discarded. Pass any PSR-3 implementation (e.g. Monolog, or
     * the opt-in {@link \AmplitudeExperiment\Logger\DefaultLogger}) to receive log output.
     */
    public ?LoggerInterface $logger;
    /**
     * The server endpoint from which to request variants.
     */
    public string $serverUrl;
    /**
     * @var array<string, mixed>
     * Bootstrap the client with a pre-fetched flag configurations.
     * Useful if you are managing the flag configurations separately.
     */
    public array $bootstrap;
    public ?AssignmentConfig $assignmentConfig;
    public ?ExposureConfig $exposureConfig;
    /**
     * The underlying HTTP client to use for requests, if this is not set, the default {@link GuzzleHttpClient} will be used.
     */
    public ?HttpClientInterface $httpClient;
    /**
     * @var array<string, mixed>
     * The configuration for the underlying default {@link GuzzleHttpClient} client (if used). See {@link GUZZLE_DEFAULTS} for defaults.
     */
    public array $guzzleClientConfig;

    const DEFAULTS = [
        'logger' => null,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => [],
        'assignmentConfig' => null,
        'exposureConfig' => null,
        'httpClient' => null,
        'guzzleClientConfig' => []
    ];

    /**
     * @param array<string, mixed> $guzzleClientConfig
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(?LoggerInterface $logger, string $serverUrl, array $bootstrap, ?AssignmentConfig $assignmentConfig, ?ExposureConfig $exposureConfig, ?HttpClientInterface $httpClient, array $guzzleClientConfig)
    {
        $this->logger = $logger;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
        $this->assignmentConfig = $assignmentConfig;
        $this->exposureConfig = $exposureConfig;
        $this->httpClient = $httpClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
