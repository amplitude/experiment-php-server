<?php

namespace AmplitudeExperiment\Exposure;

use AmplitudeExperiment\Amplitude\Amplitude;

/**
 * A default implementation of the ExposureTrackingProvider interface.
 */
class DefaultExposureTrackingProvider implements ExposureTrackingProvider
{
    private Amplitude $amplitude;

    public function __construct(Amplitude $amplitude)
    {
        $this->amplitude = $amplitude;
    }

    public function track(Exposure $exposure): void
    {
        $events = $exposure->toEvents();
        foreach ($events as $event) {
            $this->amplitude->logEvent($event);
        }
    }
}

