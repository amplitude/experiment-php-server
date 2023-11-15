<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\Event;
use function AmplitudeExperiment\hashCode;

require_once __DIR__ . '/../Util.php';

const FLAG_TYPE_MUTUAL_EXCLUSION_GROUP = 'mutual-exclusion-group';
const FLAG_TYPE_HOLDOUT_GROUP = 'holdout-group';;
const DAY_MILLIS = 24 * 60 * 60 * 1000;

class AssignmentService
{
    private Amplitude $amplitude;
    private AssignmentFilter $assignmentFilter;

    public function __construct(Amplitude $amplitude, AssignmentFilter $assignmentFilter)
    {
        $this->amplitude = $amplitude;
        $this->assignmentFilter = $assignmentFilter;
    }

    public function track(Assignment $assignment): void
    {
        if ($this->assignmentFilter->shouldTrack($assignment)) {
            $this->amplitude->logEvent($this->toEvent($assignment));
        }
    }

    public static function toEvent(Assignment $assignment): Event
    {
        $event = new Event('[Experiment] Assignment');
        $event->userId = $assignment->user->userId;
        $event->deviceId = $assignment->user->deviceId;
        $event->eventProperties = [];
        $event->userProperties = [];

        foreach ($assignment->results as $resultsKey => $result) {
            $event->eventProperties["{$resultsKey}.variant"] = $result['key'];
        }

        $set = [];
        $unset = [];
        foreach ($assignment->results as $resultsKey => $result) {
            $flagType = $result['metadata']['flagType'] ?? null;
            $default = $result['metadata']['default'] ?? false;
            if ($flagType == FLAG_TYPE_MUTUAL_EXCLUSION_GROUP) {
                continue;
            } elseif ($default) {
                $unset["[Experiment] {$resultsKey}"] = '-';
            } else {
                $set["[Experiment] {$resultsKey}"] = $result['value'];
            }
        }

        $event->userProperties['$set'] = $set;
        $event->userProperties['$unset'] = $unset;

        $hash = hashCode($assignment->canonicalize());

        $event->insertId = "{$event->userId} {$event->deviceId} {$hash} " .
            floor($assignment->timestamp / DAY_MILLIS);

        return $event;
    }
}
