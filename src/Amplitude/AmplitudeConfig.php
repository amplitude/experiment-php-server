<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Assignment\AssignmentConfigBuilder;
use AmplitudeExperiment\Http\HttpClientInterface;

/**
 * Configuration options for Amplitude. The Amplitude object is created when you create an {@link AssignmentConfig}.
 * Options should be set using {@link AssignmentConfigBuilder}.
 */
class AmplitudeConfig
{
    /**
     * The events buffered in memory will flush when exceed flushQueueSize
     * Must be positive.
     */
    public int $flushQueueSize;
    /**
     * The maximum retry attempts for an event when receiving error response.
     */
    public int $minIdLength;
    /**
     * The server zone of project. Default to 'US'. Support 'EU'.
     */
    public string $serverZone;
    /**
     * API endpoint url. Default to None. Auto selected by configured server_zone
     */
    public string $serverUrl;
    /**
     * True to use batch API endpoint, False to use HTTP V2 API endpoint.
     */
    public bool $useBatch;
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
        'serverZone' => 'US',
        'serverUrl' => [
            'EU' => [
                'batch' => 'https://api.eu.amplitude.com/batch',
                'v2' => 'https://api.eu.amplitude.com/2/httpapi'
            ],
            'US' => [
                'batch' => 'https://api2.amplitude.com/batch',
                'v2' => 'https://api2.amplitude.com/2/httpapi'
            ]
        ],
        'useBatch' => false,
        'minIdLength' => 5,
        'flushQueueSize' => 200,
        'flushMaxRetries' => 12,
        'httpClient' => null,
        'guzzleClientConfig' => []
    ];

    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function __construct(
        int                  $flushQueueSize,
        int                  $minIdLength,
        string               $serverZone,
        string               $serverUrl,
        bool                 $useBatch,
        ?HttpClientInterface $httpClient,
        array                $guzzleClientConfig
    )
    {
        $this->flushQueueSize = $flushQueueSize;
        $this->minIdLength = $minIdLength;
        $this->serverZone = $serverZone;
        $this->serverUrl = $serverUrl;
        $this->useBatch = $useBatch;
        $this->httpClient = $httpClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): AmplitudeConfigBuilder
    {
        return new AmplitudeConfigBuilder();
    }
}
