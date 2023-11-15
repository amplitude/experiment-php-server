<?php

namespace AmplitudeExperiment\Assignment;

class AssignmentConfig
{
    public string $apiKey;
    public int $cacheCapacity;

    public function __construct(string $apiKey, int $cacheCapacity = 65536)
    {
        $this->apiKey = $apiKey;
        $this->cacheCapacity = $cacheCapacity;
    }
}
