<?php

namespace AmplitudeExperiment\Exposure;

/**
 * An interface for tracking exposures to Amplitude Experiment during local evaluation.
 *
 * Implementations of this interface are responsible for sending exposure events to Amplitude.
 * {@link DefaultExposureTrackingProvider} is provided as a default synchronous implementation.
 */
interface ExposureTrackingProvider
{
    /**
     * Called when {@link LocalEvaluationClient} intends to track an Exposure event
     *
     * Use {@link Exposure::toEvents()} to convert the Exposure to an array of Event objects,
     * which can then be serialized or processed as needed for sending to Amplitude.
     *
     * @param Exposure $events
     */
    public function track(Exposure $events): void;
}

