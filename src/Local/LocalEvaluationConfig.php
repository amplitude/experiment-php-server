<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Exposure\ExposureConfig;
use AmplitudeExperiment\Http\RetryConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
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

    const DEFAULTS = [
        'logger' => null,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => [],
        'assignmentConfig' => null,
        'exposureConfig' => null,
        'httpClient' => null,
        'requestFactory' => null,
        'retryConfig' => null,
    ];

    /**
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(
        ?LoggerInterface $logger,
        string $serverUrl,
        array $bootstrap,
        ?AssignmentConfig $assignmentConfig,
        ?ExposureConfig $exposureConfig,
        ?ClientInterface $httpClient,
        ?RequestFactoryInterface $requestFactory,
        ?RetryConfig $retryConfig
    ) {
        $this->logger = $logger;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
        $this->assignmentConfig = $assignmentConfig;
        $this->exposureConfig = $exposureConfig;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->retryConfig = $retryConfig;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
