<?php

namespace AmplitudeExperiment\Assignment;

use Symfony\Component\Cache\Adapter\ArrayAdapter;

class AssignmentConfigBuilder
{
    protected int $cacheCapacity = AssignmentConfig::DEFAULTS['cacheCapacity'];
    protected AssignmentTrackingProvider $assignmentTrackingProvider;
    protected string $apiKey;
    protected int $minIdLength = AssignmentConfig::DEFAULTS['minIdLength'];
    protected ?AssignmentFilter $assignmentFilter;

    public function __construct(string $apiKey, AssignmentTrackingProvider $assignmentTrackingProvider)
    {
        $this->apiKey = $apiKey;
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
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

    public function assignmentFilter(AssignmentFilter $assignmentFilter): AssignmentConfigBuilder
    {
        $this->assignmentFilter = $assignmentFilter;
        return $this;
    }

    public function build(): AssignmentConfig
    {
        if ($this->assignmentFilter === null) {
            $this->assignmentFilter = new DefaultAssignmentFilter(new ArrayAdapter(0,0,0,$this->cacheCapacity));
        }
        return new AssignmentConfig(
            $this->apiKey,
            $this->cacheCapacity,
            $this->assignmentTrackingProvider,
            $this->minIdLength,
            $this->assignmentFilter
        );
    }
}
