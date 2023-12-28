<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Http\FetchClientInterface;
use AmplitudeExperiment\Logger\LogLevel;
use Psr\Log\LoggerInterface;

class LocalEvaluationConfig
{
    /**
     * Set to use custom logger. If not set, a {@link DefaultLogger} is used.
     */
    public ?LoggerInterface $logger;
    /**
     * The log level to use for the logger.
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
     * The underlying HTTP client to use for requests.
     */
    public ?FetchClientInterface $fetchClient;
    /**
     * @var array<string, mixed>
     * The configuration for the underlying default Guzzle client.
     */
    public array $guzzleClientConfig;

    const DEFAULTS = [
        'logger' => null,
        'logLevel' => LogLevel::INFO,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => [],
        'assignmentConfig' => null,
        'fetchClient' => null,
        'guzzleClientConfig' => []
    ];

    /**
     * @param array<string, mixed> $guzzleClientConfig
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(?LoggerInterface $logger, int $logLevel, string $serverUrl, array $bootstrap, ?AssignmentConfig $assignmentConfig, ?FetchClientInterface $fetchClient, array $guzzleClientConfig)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
        $this->assignmentConfig = $assignmentConfig;
        $this->fetchClient = $fetchClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
