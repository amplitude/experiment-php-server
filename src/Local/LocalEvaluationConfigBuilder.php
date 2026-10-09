<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Exposure\ExposureConfig;
use AmplitudeExperiment\Http\RetryConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

class LocalEvaluationConfigBuilder
{
    protected ?LoggerInterface $logger = LocalEvaluationConfig::DEFAULTS['logger'];
    protected string $serverUrl = LocalEvaluationConfig::DEFAULTS['serverUrl'];
    /**
     * @var array<string, mixed>
     */
    protected array $bootstrap = LocalEvaluationConfig::DEFAULTS['bootstrap'];
    protected ?AssignmentConfig $assignmentConfig = LocalEvaluationConfig::DEFAULTS['assignmentConfig'];
    protected ?ExposureConfig $exposureConfig = LocalEvaluationConfig::DEFAULTS['exposureConfig'];
    protected ?ClientInterface $httpClient = LocalEvaluationConfig::DEFAULTS['httpClient'];
    protected ?RequestFactoryInterface $requestFactory = LocalEvaluationConfig::DEFAULTS['requestFactory'];
    protected ?RetryConfig $retryConfig = LocalEvaluationConfig::DEFAULTS['retryConfig'];

    public function __construct()
    {
    }

    public function logger(LoggerInterface $logger): LocalEvaluationConfigBuilder
    {
        $this->logger = $logger;
        return $this;
    }

    public function serverUrl(string $serverUrl): LocalEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    /**
     * @param array<string, mixed> $bootstrap
     */
    public function bootstrap(array $bootstrap): LocalEvaluationConfigBuilder
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    public function assignmentConfig(AssignmentConfig $assignmentConfig): LocalEvaluationConfigBuilder
    {
        $this->assignmentConfig = $assignmentConfig;
        return $this;
    }

    public function exposureConfig(ExposureConfig $exposureConfig): LocalEvaluationConfigBuilder
    {
        $this->exposureConfig = $exposureConfig;
        return $this;
    }

    /**
     * Supply a PSR-18 HTTP client. The SDK uses it verbatim — no retry wrap.
     * If omitted, a client is auto-discovered and wrapped in
     * {@link \AmplitudeExperiment\Http\RetryingClient} using {@link retryConfig}.
     */
    public function httpClient(ClientInterface $httpClient): LocalEvaluationConfigBuilder
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Supply a PSR-17 request factory. If omitted, a factory is auto-discovered.
     */
    public function requestFactory(RequestFactoryInterface $requestFactory): LocalEvaluationConfigBuilder
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * Configure retry behavior for the auto-discovered client. Ignored when
     * a client is supplied via {@link httpClient()}.
     */
    public function retryConfig(RetryConfig $retryConfig): LocalEvaluationConfigBuilder
    {
        $this->retryConfig = $retryConfig;
        return $this;
    }

    public function build(): LocalEvaluationConfig
    {
        return new LocalEvaluationConfig(
            $this->logger,
            $this->serverUrl,
            $this->bootstrap,
            $this->assignmentConfig,
            $this->exposureConfig,
            $this->httpClient,
            $this->requestFactory,
            $this->retryConfig
        );
    }
}
