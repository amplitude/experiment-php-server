<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\Http\RetryConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

class RemoteEvaluationConfigBuilder
{
    protected ?LoggerInterface $logger = RemoteEvaluationConfig::DEFAULTS['logger'];
    protected bool $debug = RemoteEvaluationConfig::DEFAULTS['debug'];
    protected string $serverUrl = RemoteEvaluationConfig::DEFAULTS['serverUrl'];
    protected ?ClientInterface $httpClient = RemoteEvaluationConfig::DEFAULTS['httpClient'];
    protected ?RequestFactoryInterface $requestFactory = RemoteEvaluationConfig::DEFAULTS['requestFactory'];
    protected ?RetryConfig $retryConfig = RemoteEvaluationConfig::DEFAULTS['retryConfig'];

    public function __construct()
    {
    }

    public function logger(LoggerInterface $logger): RemoteEvaluationConfigBuilder
    {
        $this->logger = $logger;
        return $this;
    }

    public function serverUrl(string $serverUrl): RemoteEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    /**
     * Supply a PSR-18 HTTP client. The SDK uses it verbatim — no retry wrap.
     * If omitted, a client is auto-discovered and wrapped in
     * {@link \AmplitudeExperiment\Http\RetryingClient} using {@link retryConfig}.
     */
    public function httpClient(ClientInterface $httpClient): RemoteEvaluationConfigBuilder
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Supply a PSR-17 request factory. If omitted, a factory is auto-discovered.
     */
    public function requestFactory(RequestFactoryInterface $requestFactory): RemoteEvaluationConfigBuilder
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * Configure retry behavior for the auto-discovered client. Ignored when
     * a client is supplied via {@link httpClient()}.
     */
    public function retryConfig(RetryConfig $retryConfig): RemoteEvaluationConfigBuilder
    {
        $this->retryConfig = $retryConfig;
        return $this;
    }

    public function build(): RemoteEvaluationConfig
    {
        return new RemoteEvaluationConfig(
            $this->logger,
            $this->serverUrl,
            $this->httpClient,
            $this->requestFactory,
            $this->retryConfig
        );
    }
}
