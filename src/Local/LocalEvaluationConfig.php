<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Http\HttpClientInterface;
use AmplitudeExperiment\Logger\LogLevel;
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
     * Set to use custom logger. If not set, a {@link DefaultLogger} is used.
     */
    public ?LoggerInterface $logger;
    /**
     * The {@link LogLevel} to use for the logger.
     */
    public int $logLevel;
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
        'logLevel' => LogLevel::INFO,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => [],
        'assignmentConfig' => null,
        'httpClient' => null,
        'guzzleClientConfig' => []
    ];

    /**
     * @param array<string, mixed> $guzzleClientConfig
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(?LoggerInterface $logger, int $logLevel, string $serverUrl, array $bootstrap, ?AssignmentConfig $assignmentConfig, ?HttpClientInterface $httpClient, array $guzzleClientConfig)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
        $this->assignmentConfig = $assignmentConfig;
        $this->httpClient = $httpClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
