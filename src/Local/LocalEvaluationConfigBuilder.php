<?php

namespace AmplitudeExperiment\Local;

class LocalEvaluationConfigBuilder
{
    protected bool $debug = LocalEvaluationConfig::DEFAULTS['debug'];
    protected string $serverUrl = LocalEvaluationConfig::DEFAULTS['serverUrl'];
    protected array $bootstrap = LocalEvaluationConfig::DEFAULTS['bootstrap'];

    public function __construct()
    {
    }

    public function debug(bool $debug): LocalEvaluationConfigBuilder
    {
        $this->debug = $debug;
        return $this;
    }

    public function serverUrl(string $serverUrl): LocalEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function bootstrap(array $bootstrap): LocalEvaluationConfigBuilder
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    public function build(): LocalEvaluationConfig
    {
        return new LocalEvaluationConfig(
            $this->debug,
            $this->serverUrl,
            $this->bootstrap
        );
    }
}
