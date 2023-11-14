<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Analytics\Client;
use AmplitudeExperiment\Analytics\BaseEvent;
use function AmplitudeExperiment\hashCode;

require_once __DIR__ . '/../Util.php';

const FLAG_TYPE_MUTUAL_EXCLUSION_GROUP = "mutual-exclusion-group";
const DAY_SECS = 24 * 60 * 60;

class AssignmentService
{
    private Client $amplitude;
    private AssignmentFilter $assignmentFilter;

    public function __construct(Client $amplitude, AssignmentFilter $assignmentFilter)
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

    public static function toEvent(Assignment $assignment): BaseEvent
    {
        $event = new BaseEvent('[Experiment] Assignment');
        $event->userId = $assignment->user->userId;
        $event->deviceId = $assignment->user->deviceId;
        $event->eventProperties = [];
        $event->userProperties = [];

        foreach ($assignment->results as $resultsKey => $result) {
            $event->eventProperties["{$resultsKey}.variant"] = $result['value'];
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
            floor($assignment->timestamp / DAY_SECS);

        return $event;
    }
}
