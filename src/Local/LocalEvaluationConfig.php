<?php

namespace AmplitudeExperiment\Local;

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

    const DEFAULTS = [
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'bootstrap' => []
    ];

    public function __construct(bool $debug, string $serverUrl, array $bootstrap)
    {
        $this->debug = $debug;
        $this->serverUrl = $serverUrl;
        $this->bootstrap = $bootstrap;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
