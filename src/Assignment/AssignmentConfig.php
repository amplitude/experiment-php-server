<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;

/**
 * Configuration options for assignment tracking. This is an object that can be created using
 * a {@link AssignmentConfigBuilder}. Example usage:
 *
 * ```
 * AssignmentConfigBuilder::builder('api-key')->cacheCapacity(1000)->build();
 * ```
 */

class AssignmentConfig
{
    /**
     * The maximum number of assignments stored in the assignment cache
     */
    public int $cacheCapacity;
    /**
     * The provider for tracking assignment events to Amplitude
     */
    public AssignmentTrackingProvider $assignmentTrackingProvider;
    /**
     * The API key for the Amplitude project.
     */
    public string $apiKey;
    /**
     * The minimum length of the id field in events. Default to 5. This is set in {@link AmplitudeConfig} if the
     * {@link DefaultAssignmentTrackingProvider} is used, and does not need to be set here.
     */
    public int $minIdLength;

    const DEFAULTS = [
        'cacheCapacity' => 65536,
        'minIdLength' => 5,
    ];

    public function __construct(int $cacheCapacity, AssignmentTrackingProvider $assignmentTrackingProvider, string $apiKey, int $minIdLength)
    {
        $this->cacheCapacity = $cacheCapacity;
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
        $this->apiKey = $apiKey;
        $this->minIdLength = $minIdLength;
    }

    public static function builder(AssignmentTrackingProvider $assignmentTrackingProvider, string $apiKey): AssignmentConfigBuilder
    {
        return new AssignmentConfigBuilder($assignmentTrackingProvider, $apiKey);
    }
}
