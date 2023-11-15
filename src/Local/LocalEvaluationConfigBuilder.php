<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;

class LocalEvaluationConfigBuilder
{
    protected bool $debug = LocalEvaluationConfig::DEFAULTS['debug'];
    protected string $serverUrl = LocalEvaluationConfig::DEFAULTS['serverUrl'];
    protected array $bootstrap = LocalEvaluationConfig::DEFAULTS['bootstrap'];
    protected ?AssignmentConfig $assignmentConfig = LocalEvaluationConfig::DEFAULTS['assignmentConfig'];

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

    public function assignmentConfig(AssignmentConfig $assignmentConfig): LocalEvaluationConfigBuilder
    {
        $this->assignmentConfig = $assignmentConfig;
        return $this;
    }

    public function build(): LocalEvaluationConfig
    {
        return new LocalEvaluationConfig(
            $this->debug,
            $this->serverUrl,
            $this->bootstrap,
            $this->assignmentConfig
        );
    }
}
