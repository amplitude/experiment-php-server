<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Configuration options. This is an object that can be created using
 * a {@link RemoteEvaluationConfigBuilder}. Example usage:
 *
 *```
 * RemoteEvaluationConfig::builder()->serverUrl("https://api.lab.amplitude.com/")->build();
 * ```
 */
class RemoteEvaluationConfig
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
     * The underlying HTTP client to use for requests, if this is not set, the default {@link GuzzleHttpClient} will be used.
     */
    public ?HttpClientInterface $httpClient;
    /**
     * @var array<string, mixed>
     * The configuration for the underlying default {@link GuzzleHttpClient} (if used). See {@link GUZZLE_DEFAULTS} for defaults.
     */
    public array $guzzleClientConfig;

    const DEFAULTS = [
        'logger' => null,
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'httpClient' => null,
        'guzzleClientConfig' => []
    ];


    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function __construct(
        ?LoggerInterface     $logger,
        string               $serverUrl,
        ?HttpClientInterface $httpClient,
        array                $guzzleClientConfig
    )
    {
        $this->logger = $logger;
        $this->serverUrl = $serverUrl;
        $this->httpClient = $httpClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): RemoteEvaluationConfigBuilder
    {
        return new RemoteEvaluationConfigBuilder();
    }
}
