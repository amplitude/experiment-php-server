<?php

namespace AmplitudeExperiment\Remote;

use AmplitudeExperiment\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RemoteEvaluationConfigBuilder
{
    protected ?LoggerInterface $logger = RemoteEvaluationConfig::DEFAULTS['logger'];
    protected int $logLevel = RemoteEvaluationConfig::DEFAULTS['logLevel'];
    protected bool $debug = RemoteEvaluationConfig::DEFAULTS['debug'];
    protected string $serverUrl = RemoteEvaluationConfig::DEFAULTS['serverUrl'];
    protected ?HttpClientInterface $httpClient = RemoteEvaluationConfig::DEFAULTS['httpClient'];
    /**
     * @var array<string, mixed>
     */
    protected array $guzzleClientConfig = RemoteEvaluationConfig::DEFAULTS['guzzleClientConfig'];

    public function __construct()
    {
    }

    public function logger(LoggerInterface $logger): RemoteEvaluationConfigBuilder
    {
        $this->logger = $logger;
        return $this;
    }

    public function logLevel(int $logLevel): RemoteEvaluationConfigBuilder
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    public function serverUrl(string $serverUrl): RemoteEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function httpClient(HttpClientInterface $httpClient): RemoteEvaluationConfigBuilder
    {
        $this->httpClient = $httpClient;
        return $this;
    }


    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function guzzleClientConfig(array $guzzleClientConfig): RemoteEvaluationConfigBuilder
    {
        $this->guzzleClientConfig = $guzzleClientConfig;
        return $this;
    }

    public function build(): RemoteEvaluationConfig
    {
        return new RemoteEvaluationConfig(
            $this->logger,
            $this->logLevel,
            $this->serverUrl,
            $this->httpClient,
            $this->guzzleClientConfig
        );
    }
}
