<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\Http\FetchClientInterface;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Configuration options. This is an object that can be created using
 * a {@link RemoteEvaluationConfigBuilder}. Example usage:
 *
 *`RemoteEvaluationConfig::builder()->serverUrl("https://api.lab.amplitude.com/")->build()`
 */
class RemoteEvaluationConfig
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
     * The underlying HTTP client to use for requests.
     */
    public ?FetchClientInterface $fetchClient;
    /**
     * @var array<string, mixed>
     * The configuration for the underlying default Guzzle client. See {@link GUZZLE_DEFAULTS} for defaults.
     */
    public array $guzzleClientConfig;

    const DEFAULTS = [
        'logger' => null,
        'logLevel' => LogLevel::INFO,
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'fetchClient' => null,
        'guzzleClientConfig' => []
    ];


    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function __construct(
        ?LoggerInterface      $logger,
        int                   $logLevel,
        string                $serverUrl,
        ?FetchClientInterface $fetchClient,
        array                 $guzzleClientConfig
    )
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
        $this->serverUrl = $serverUrl;
        $this->fetchClient = $fetchClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): RemoteEvaluationConfigBuilder
    {
        return new RemoteEvaluationConfigBuilder();
    }
}
