<?php

namespace AmplitudeExperiment\Assignment;

class AssignmentConfigBuilder
{
    protected int $cacheCapacity = AssignmentConfig::DEFAULTS['cacheCapacity'];
    protected AssignmentTrackingProvider $assignmentTrackingProvider;
    protected string $apiKey;
    protected int $minIdLength = AssignmentConfig::DEFAULTS['minIdLength'];

    public function __construct(AssignmentTrackingProvider $assignmentTrackingProvider, string $apiKey)
    {
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
        $this->apiKey = $apiKey;
    }

    public function cacheCapacity(int $cacheCapacity): AssignmentConfigBuilder
    {
        $this->cacheCapacity = $cacheCapacity;
        return $this;
    }

    public function minIdLength(int $minIdLength): AssignmentConfigBuilder
    {
        $this->minIdLength = $minIdLength;
        return $this;
    }

    public function build(): AssignmentConfig
    {
        return new AssignmentConfig(
            $this->cacheCapacity,
            $this->assignmentTrackingProvider,
            $this->apiKey,
            $this->minIdLength
        );
    }
}
