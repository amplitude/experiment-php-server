<?php

namespace AmplitudeExperiment\Local;

class LocalEvaluationConfigBuilder
{
    protected ?bool $debug = false;
    protected ?string $serverUrl = 'https://api.lab.amplitude.com';
    protected ?array $bootstrap = [];
    protected ?int $flagConfigPollingIntervalMillis = 30000;

    public function __construct()
    {
    }

    public function debug(?bool $debug): LocalEvaluationConfigBuilder
    {
        $this->debug = $debug;
        return $this;
    }

    public function serverUrl(?string $serverUrl): LocalEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function bootstrap(?array $bootstrap): LocalEvaluationConfigBuilder
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    public function flagConfigPollingIntervalMillis(?int $flagConfigPollingIntervalMillis): LocalEvaluationConfigBuilder
    {
        $this->flagConfigPollingIntervalMillis = $flagConfigPollingIntervalMillis;
        return $this;
    }

    public function build(): LocalEvaluationConfig
    {
        return new LocalEvaluationConfig($this);
    }
}
