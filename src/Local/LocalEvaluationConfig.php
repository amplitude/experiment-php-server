<?php

namespace AmplitudeExperiment\Local;

class LocalEvaluationConfig
{
    public bool $debug;
    public string $serverUrl;
    public int $flagConfigPollingIntervalMillis;

    const DEFAULTS = [
        'debug' => false,
        'serverUrl' => 'https://api.lab.amplitude.com',
        'flagConfigPollingIntervalMillis' => 30000,
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
