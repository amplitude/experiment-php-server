<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;

class LocalEvaluationConfig
{
    /**
     * Set to true to log some extra information to the console.
     */
    public bool $debug;
    /**
     * The server endpoint from which to request variants.
     */
    public string $serverUrl;
    /**
     * Bootstrap the client with a pre-fetched flag configurations.
     *
     * Useful if you are managing the flag configurations separately.
     */
    public array $bootstrap;
    public ?AssignmentConfig $assignmentConfig;

    const DEFAULTS = [
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => [],
        'assignmentConfig' => null
    ];

    public function __construct(bool $debug, string $serverUrl, array $bootstrap, ?AssignmentConfig $assignmentConfig)
    {
        $this->debug = $debug;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
        $this->assignmentConfig = $assignmentConfig;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
