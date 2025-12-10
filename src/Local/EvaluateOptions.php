<?php

namespace AmplitudeExperiment\Local;

/**
 * Options for evaluating variants for a user.
 */
class EvaluateOptions
{
    /**
     * Whether to track exposure event for the evaluation.
     */
    public ?bool $tracksExposure;

    /**
     * @param bool|null $tracksExposure
     */
    public function __construct(?bool $tracksExposure = null)
    {
        $this->tracksExposure = $tracksExposure;
    }
}

