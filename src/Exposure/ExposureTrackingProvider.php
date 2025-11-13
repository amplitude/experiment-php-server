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
     * Use {@link Exposure::toJSONPayload()} to convert the Exposure to a JSON string which can be used as a payload
     * to the Amplitude event upload API.
     *
     * Use {@link Exposure::toArray()} to convert the Exposure to an array representation of an Amplitude event
     *
     * Used {@link Exposure::toJSONString()} to convert the Exposure to an Amplitude event JSON string
     *
     * @param Exposure $events
     */
    public function track(Exposure $events): void;
}

