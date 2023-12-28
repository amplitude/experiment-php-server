<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Http\FetchClientInterface;

/**
 * Configuration options for Amplitude. This is an object that can be created using
 * a {@link AmplitudeConfigBuilder}. Example usage:
 *
 * AmplitudeConfigBuilder::builder()->serverZone("EU")->build();
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
    public ?FetchClientInterface $fetchClient;
    /**
     * @var array<string, mixed>
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
        'fetchClient' => null,
        'guzzleClientConfig' => []
    ];

    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function __construct(
        int    $flushQueueSize,
        int    $minIdLength,
        string $serverZone,
        string $serverUrl,
        bool   $useBatch,
        ?FetchClientInterface $fetchClient,
        array  $guzzleClientConfig
    )
    {
        $this->flushQueueSize = $flushQueueSize;
        $this->minIdLength = $minIdLength;
        $this->serverZone = $serverZone;
        $this->serverUrl = $serverUrl;
        $this->useBatch = $useBatch;
        $this->fetchClient = $fetchClient;
        $this->guzzleClientConfig = $guzzleClientConfig;
    }

    public static function builder(): AmplitudeConfigBuilder
    {
        return new AmplitudeConfigBuilder();
    }
}
