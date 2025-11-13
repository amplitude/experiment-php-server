<?php

namespace AmplitudeExperiment\Local;

/**
 * Options for evaluating variants for a user.
 */
class EvaluateOptions
{
    /**
     * The flags to evaluate with the user. If null or empty, all flags are evaluated.
     * @var array<string>|null
     */
    public ?array $flagKeys;

    /**
     * Whether to track exposure event for the evaluation.
     */
    public ?bool $tracksExposure;

    /**
     * @param array<string>|null $flagKeys
     * @param bool|null $tracksExposure
     */
    public function __construct(?array $flagKeys = null, ?bool $tracksExposure = null)
    {
        $this->flagKeys = $flagKeys;
        $this->tracksExposure = $tracksExposure;
    }
}

