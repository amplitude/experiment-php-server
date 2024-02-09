<?php

namespace AmplitudeExperiment\Assignment;

class AssignmentConfigBuilder
{
    protected int $cacheCapacity = AssignmentConfig::DEFAULTS['cacheCapacity'];
    protected AssignmentTrackingProvider $assignmentTrackingProvider;

    public function __construct(AssignmentTrackingProvider $assignmentTrackingProvider)
    {
    }

    public function cacheCapacity(int $cacheCapacity): AssignmentConfigBuilder
    {
        $this->cacheCapacity = $cacheCapacity;
        return $this;
    }

    public function build(): AssignmentConfig
    {
        return new AssignmentConfig(
            $this->cacheCapacity,
            $this->assignmentTrackingProvider
        );
    }
}
