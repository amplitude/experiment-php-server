<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\AmplitudeConfigBuilder;

/**
 * Extends AmplitudeConfigBuilder to allow configuration {@link AmplitudeConfig} of underlying {@link Amplitude} client.
 */
class AssignmentConfigBuilder extends AmplitudeConfigBuilder
{
    protected string $apiKey;
    protected int $cacheCapacity = AssignmentConfig::DEFAULTS['cacheCapacity'];

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    public function cacheCapacity(int $cacheCapacity): AssignmentConfigBuilder
    {
        $this->cacheCapacity = $cacheCapacity;
        return $this;
    }

    public function build()
    {
        return new AssignmentConfig(
            $this->apiKey,
            $this->cacheCapacity,
            parent::build()
        );
    }
}
