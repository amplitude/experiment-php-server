<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Assignment\AssignmentConfigBuilder;
use AmplitudeExperiment\Http\RetryConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

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
     * The minimum length of the id field in events. Default to 5.
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
     * The PSR-18 HTTP client to use for requests. If null, a PSR-18
     * implementation is auto-discovered via php-http/discovery and wrapped
     * in {@link \AmplitudeExperiment\Http\RetryingClient} using
     * {@link $retryConfig}. A user-supplied client is used verbatim with
     * no retry wrap.
     */
    public ?ClientInterface $httpClient;
    /**
     * The PSR-17 request factory used to construct requests. If null, a
     * PSR-17 factory is auto-discovered.
     */
    public ?RequestFactoryInterface $requestFactory;
    /**
     * Retry configuration for the auto-wrapped client. Ignored when
     * {@link $httpClient} is supplied — the user's client is used verbatim.
     */
    public ?RetryConfig $retryConfig;
    /**
     * Set to use a custom PSR-3 logger. If not set, a {@link \Psr\Log\NullLogger} is used
     * and SDK log messages are discarded. Pass any PSR-3 implementation (e.g. Monolog, or
     * the opt-in {@link \AmplitudeExperiment\Logger\DefaultLogger}) to receive log output.
     */
    public ?LoggerInterface $logger;

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
        'httpClient' => null,
        'requestFactory' => null,
        'retryConfig' => null,
        'logger' => null,
    ];

    public function __construct(
        int $flushQueueSize,
        int $minIdLength,
        string $serverZone,
        string $serverUrl,
        bool $useBatch,
        ?ClientInterface $httpClient,
        ?RequestFactoryInterface $requestFactory,
        ?RetryConfig $retryConfig,
        ?LoggerInterface $logger
    ) {
        $this->flushQueueSize = $flushQueueSize;
        $this->minIdLength = $minIdLength;
        $this->serverZone = $serverZone;
        $this->serverUrl = $serverUrl;
        $this->useBatch = $useBatch;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->retryConfig = $retryConfig;
        $this->logger = $logger;
    }

    public static function builder(): AmplitudeConfigBuilder
    {
        return new AmplitudeConfigBuilder();
    }
}
