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
     * The maximum number of assignments stored in the assignment cache
     */
    public int $cacheCapacity;
    public AssignmentTrackingProvider $assignmentTrackingProvider;

    const DEFAULTS = [
        'cacheCapacity' => 65536,
    ];

    public function __construct(int $cacheCapacity, AssignmentTrackingProvider $assignmentTrackingProvider)
    {
        $this->cacheCapacity = $cacheCapacity;
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
    }

    public static function builder(AssignmentTrackingProvider $assignmentTrackingProvider): AssignmentConfigBuilder
    {
        return new AssignmentConfigBuilder($assignmentTrackingProvider);
    }
}
