<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;

/**
 * Configuration options for assignment tracking. This is an object that can be created using
 * a {@link AssignmentConfigBuilder}, which also sets options for {@link AmplitudeConfig}. Example usage:
 *
 * ```
 * AssignmentConfigBuilder::builder('api-key')->minIdLength(10)->build();
 * ```
 */

class AssignmentConfig
{
    /**
     * The Amplitude Analytics API key.
     */
    public string $apiKey;
    /**
     * The maximum number of assignments stored in the assignment cache
     */
    public int $cacheCapacity;
    /**
     * Configuration options for the underlying {@link Amplitude} client. This is created when
     * calling {@link AssignmentConfigBuilder::build()} and does not need to be explicitly set.
     */
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
