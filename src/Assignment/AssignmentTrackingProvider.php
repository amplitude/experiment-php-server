<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\Event;

/**
 * An interface for tracking assignments to Amplitude Experiment.
 *
 * See {@link https://www.docs.developers.amplitude.com/analytics/apis/batch-event-upload-api/} for more information
 * on how to send events to Amplitude.
 *
 * Use {@link Event::toArray()} to convert an event to the proper array format before sending it to Amplitude.
 *
 * {@link DefaultAssignmentTrackingProvider} is provided as a default implementation. However, a custom implementation
 * is recommended for increased flexibility and efficiency.
 */

interface AssignmentTrackingProvider
{
    public function track(Event $event): void;
}
