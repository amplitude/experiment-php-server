<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\Event;

/**
 * A default implementation of the AssignmentTrackingProvider interface.
 */
class DefaultAssignmentTrackingProvider implements AssignmentTrackingProvider
{
    private Amplitude $amplitude;
    public function __construct(Amplitude $amplitude)
    {
        $this->amplitude = $amplitude;
    }

    public function track(Assignment $assignment): void
    {
        $this->amplitude->logEvent($assignment->toEvent());
    }
}
