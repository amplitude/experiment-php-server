<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Http\RetryConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

class AmplitudeConfigBuilder
{
    protected int $flushQueueSize = AmplitudeConfig::DEFAULTS['flushQueueSize'];
    protected int $minIdLength = AmplitudeConfig::DEFAULTS['minIdLength'];
    protected string $serverZone = AmplitudeConfig::DEFAULTS['serverZone'];
    protected ?string $serverUrl = null;
    protected bool $useBatch = AmplitudeConfig::DEFAULTS['useBatch'];
    protected ?ClientInterface $httpClient = AmplitudeConfig::DEFAULTS['httpClient'];
    protected ?RequestFactoryInterface $requestFactory = AmplitudeConfig::DEFAULTS['requestFactory'];
    protected ?RetryConfig $retryConfig = AmplitudeConfig::DEFAULTS['retryConfig'];
    protected ?LoggerInterface $logger = AmplitudeConfig::DEFAULTS['logger'];

    public function __construct()
    {
    }

    public function flushQueueSize(int $flushQueueSize): AmplitudeConfigBuilder
    {
        $this->flushQueueSize = $flushQueueSize;
        return $this;
    }

    public function minIdLength(int $minIdLength): AmplitudeConfigBuilder
    {
        $this->minIdLength = $minIdLength;
        return $this;
    }

    public function serverZone(string $serverZone): AmplitudeConfigBuilder
    {
        $this->serverZone = $serverZone;
        return $this;
    }

    public function serverUrl(string $serverUrl): AmplitudeConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function useBatch(bool $useBatch): AmplitudeConfigBuilder
    {
        $this->useBatch = $useBatch;
        return $this;
    }

    /**
     * Supply a PSR-18 HTTP client. The SDK uses it verbatim — no retry wrap.
     * If omitted, a client is auto-discovered and wrapped in
     * {@link \AmplitudeExperiment\Http\RetryingClient} using {@link retryConfig}.
     */
    public function httpClient(ClientInterface $httpClient): AmplitudeConfigBuilder
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Supply a PSR-17 request factory. If omitted, a factory is auto-discovered.
     */
    public function requestFactory(RequestFactoryInterface $requestFactory): AmplitudeConfigBuilder
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * Configure retry behavior for the auto-discovered client. Ignored when
     * a client is supplied via {@link httpClient()}.
     */
    public function retryConfig(RetryConfig $retryConfig): AmplitudeConfigBuilder
    {
        $this->retryConfig = $retryConfig;
        return $this;
    }

    public function logger(LoggerInterface $logger): AmplitudeConfigBuilder
    {
        $this->logger = $logger;
        return $this;
    }

    public function build(): AmplitudeConfig
    {
        if (!$this->serverUrl) {
            if ($this->useBatch) {
                $this->serverUrl = AmplitudeConfig::DEFAULTS['serverUrl'][$this->serverZone]['batch'];
            } else {
                $this->serverUrl = AmplitudeConfig::DEFAULTS['serverUrl'][$this->serverZone]['v2'];
            }
        }
        return new AmplitudeConfig(
            $this->flushQueueSize,
            $this->minIdLength,
            $this->serverZone,
            $this->serverUrl,
            $this->useBatch,
            $this->httpClient,
            $this->requestFactory,
            $this->retryConfig,
            $this->logger
        );
    }
}
