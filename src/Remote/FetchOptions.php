<?php

namespace AmplitudeExperiment\Remote;

/**
 * Options to modify the behavior of a remote evaluation fetch request.
 */
class FetchOptions
{
    /**
     * Specific flag keys to evaluate and set variants for.
     *
     * @var string[]
     */
    public array $flagKeys = [];

    /**
     * Whether to track the assignment event.
     *
     * @var ?bool
     */
    public ?bool $tracksAssignment = null;

    /**
     * Whether to track the exposure event.
     *
     * @var ?bool
     */
    public ?bool $tracksExposure = null;

    /**
     * FetchOptions constructor.
     *
     * @param string[] $flagKeys Specific flag keys to evaluate and set variants for.
     * @param ?bool $tracksAssignment Whether to track the assignment event.
     * @param ?bool $tracksExposure Whether to track the exposure event.
     */
    public function __construct(?array $flagKeys = [], ?bool $tracksAssignment = null, ?bool $tracksExposure = null)
    {
        $this->flagKeys = $flagKeys;
        $this->tracksAssignment = $tracksAssignment;
        $this->tracksExposure = $tracksExposure;
    }
}
