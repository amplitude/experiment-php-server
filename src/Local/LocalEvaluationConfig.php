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
     * The interval in milliseconds to poll the amplitude server for flag config
     * updates. These rules stored in memory and used when calling evaluate() to
     * perform local evaluation.
     *
     * Default: 30000 (30 seconds)
     */
    public int $flagConfigPollingIntervalMillis;
    /**
     * Bootstrap the client with a pre-fetched flag configurations.
     *
     * Useful if you are managing the flag configurations separately.
     */
    public array $bootstrap;

    const DEFAULTS = [
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'flagConfigPollingIntervalMillis' => 30000,
        'bootstrap' => []
    ];

    public function __construct(bool $debug, string $serverUrl, int $flagConfigPollingIntervalMillis)
    {
        $this->debug = $debug;
        $this->serverUrl = $serverUrl;
        $this->flagConfigPollingIntervalMillis = $flagConfigPollingIntervalMillis;
    }

    public static function builder(): LocalEvaluationConfigBuilder
    {
        return new LocalEvaluationConfigBuilder();
    }
}
