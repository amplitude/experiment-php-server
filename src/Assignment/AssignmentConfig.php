<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;

/**
 * Configuration options for assignment tracking. This is an object that can be created using
 * a {@link AssignmentConfigBuilder}. Example usage:
 *
 * AssignmentConfigBuilder::builder('api-key')->build()
 */

class AssignmentConfig
{
    public string $apiKey;
    public int $cacheCapacity;
    public AmplitudeConfig $amplitudeConfig;

    const DEFAULTS = [
        'cacheCapacity' => 65536,
    ];

    public function __construct(string $apiKey, int $cacheCapacity, AmplitudeConfig $amplitudeConfig)
    {
        $this->apiKey = $apiKey;
        $this->cacheCapacity = $cacheCapacity;
        $this->amplitudeConfig = $amplitudeConfig;
    }

    public static function builder(string $apiKey): AssignmentConfigBuilder
    {
        return new AssignmentConfigBuilder($apiKey);
    }
}
