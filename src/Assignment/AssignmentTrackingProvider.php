<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Local\LocalEvaluationClient;

/**
 * An interface for tracking assignments to Amplitude Experiment during local evaluation.
 *
 * See {@link https://www.docs.developers.amplitude.com/analytics/apis/batch-event-upload-api/} for more information
 * on how to send events to Amplitude.
 *
 * {@link DefaultAssignmentTrackingProvider} is provided as a default synchronous implementation.
 * However, a custom implementation is recommended for increased flexibility and efficiency.
 */

interface AssignmentTrackingProvider
{
    /**
     * Called when {@link LocalEvaluationClient} intends to track an Assignment event
     *
     * Use {@link Assignment::toJSONPayload()} to convert the Assignment to a JSON string which can be used as a payload
     * to the Amplitude event upload API.
     *
     * Use {@link Assignment::toJSONArray()} to convert the Assignment to an array representation of an Amplitude event
     *
     * Used {@link Assignment::toJSONString()} to convert the Assignment to an Amplitude event JSON string
     *
     */
    public function track(Assignment $assignment): void;
}
