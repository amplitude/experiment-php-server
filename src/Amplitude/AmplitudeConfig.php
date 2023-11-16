<?php

namespace AmplitudeExperiment\Amplitude;

class AmplitudeConfig
{
    public int $flushQueueSize;
    public int $flushMaxRetries;
    public string $serverZone;
    public string $serverUrl;
    public string $useBatch;

    const DEFAULTS = [
        'serverZone' => 'US',
        'serverUrl' => [
            'EU' => [
                'batch' => 'https://api.eu.amplitude.com/batch',
                'v2' => 'https://api.eu.amplitude.com/2/httpapi'
            ],
            'US' => [
                'batch' => 'https://api2.amplitude.com/batch',
                'v2' => 'https://api2.amplitude.com/2/httpapi'
            ]
        ],
        'useBatch' => false,
        'flushQueueSize' => 200,
        'flushMaxRetries' => 12,
    ];

    public function __construct(
        int    $flushQueueSize,
        int    $flushMaxRetries,
        string $serverZone,
        string $serverUrl,
        bool   $useBatch
    )
    {
        $this->flushQueueSize = $flushQueueSize;
        $this->flushMaxRetries = $flushMaxRetries;
        $this->serverZone = $serverZone;
        $this->serverUrl = $serverUrl;
        $this->useBatch = $useBatch;
    }

    public static function builder(): AmplitudeConfigBuilder
    {
        return new AmplitudeConfigBuilder();
    }
}
