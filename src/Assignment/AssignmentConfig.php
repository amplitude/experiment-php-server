<?php

namespace AmplitudeExperiment\Assignment;

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
     * The Amplitude Project API key.
     */
    public string $apiKey;
    /**
     * The maximum number of assignments stored in the assignment cache
     */
    public int $cacheCapacity;
    /**
     * The provider for tracking assignment events to Amplitude
     */
    public AssignmentTrackingProvider $assignmentTrackingProvider;
    /**
     * The minimum length of the id field in events. Default to 5. This is set in {@link AmplitudeConfig} if the
     * {@link DefaultAssignmentTrackingProvider} is used, and does not need to be set here.
     */
    public int $minIdLength;

    const DEFAULTS = [
        'cacheCapacity' => 65536,
        'minIdLength' => 5,
    ];

    public function __construct(string $apiKey, int $cacheCapacity, AssignmentTrackingProvider $assignmentTrackingProvider, int $minIdLength)
    {
        $this->apiKey = $apiKey;
        $this->cacheCapacity = $cacheCapacity;
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
        $this->minIdLength = $minIdLength;
    }

    public static function builder(string $apiKey, AssignmentTrackingProvider $assignmentTrackingProvider): AssignmentConfigBuilder
    {
        return new AssignmentConfigBuilder($apiKey, $assignmentTrackingProvider);
    }
}
