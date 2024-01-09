<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;

class LocalEvaluationConfigBuilder
{
    protected ?LoggerInterface $logger = LocalEvaluationConfig::DEFAULTS['logger'];
    protected int $logLevel = LocalEvaluationConfig::DEFAULTS['logLevel'];
    protected string $serverUrl = LocalEvaluationConfig::DEFAULTS['serverUrl'];
    /**
     * @var array<string, mixed>
     */
    protected array $bootstrap = LocalEvaluationConfig::DEFAULTS['bootstrap'];
    protected ?AssignmentConfig $assignmentConfig = LocalEvaluationConfig::DEFAULTS['assignmentConfig'];
    protected ?HttpClientInterface $fetchClient = LocalEvaluationConfig::DEFAULTS['fetchClient'];
    /**
     * @var array<string, mixed>
     */
    protected array $guzzleClientConfig = LocalEvaluationConfig::DEFAULTS['guzzleClientConfig'];

    public function __construct()
    {
    }

    public function logger(LoggerInterface $logger): LocalEvaluationConfigBuilder
    {
        $this->logger = $logger;
        return $this;
    }

    public function logLevel(int $logLevel): LocalEvaluationConfigBuilder
    {
        $this->logLevel = $logLevel;
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

    public function fetchClient(HttpClientInterface $fetchClient): LocalEvaluationConfigBuilder
    {
        $this->fetchClient = $fetchClient;
        return $this;
    }

    /**
     * @param array<string, mixed> $guzzleClientConfig
     */
    public function guzzleClientConfig(array $guzzleClientConfig): LocalEvaluationConfigBuilder
    {
        $this->guzzleClientConfig = $guzzleClientConfig;
        return $this;
    }

    public function build(): LocalEvaluationConfig
    {
        return new LocalEvaluationConfig(
            $this->logger,
            $this->logLevel,
            $this->serverUrl,
            $this->bootstrap,
            $this->assignmentConfig,
            $this->fetchClient,
            $this->guzzleClientConfig
        );
    }
}
