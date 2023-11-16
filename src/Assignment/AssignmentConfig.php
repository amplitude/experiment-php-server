<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;

class AssignmentConfig
{
    public string $apiKey;
    public int $cacheCapacity;
    public ?AmplitudeConfig $amplitudeConfig;

    public function __construct(string $apiKey, int $cacheCapacity = 65536, ?AmplitudeConfig $amplitudeConfig = null)
    {
        $this->apiKey = $apiKey;
        $this->cacheCapacity = $cacheCapacity;
        $this->amplitudeConfig = $amplitudeConfig ?? AmplitudeConfig::builder()->build();
    }
}
