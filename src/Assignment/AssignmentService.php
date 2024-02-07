<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\Event;
use Exception;
use function AmplitudeExperiment\hashCode;

require_once __DIR__ . '/../Util.php';

const FLAG_TYPE_MUTUAL_EXCLUSION_GROUP = 'mutual-exclusion-group';
const DAY_MILLIS = 24 * 60 * 60 * 1000;

class AssignmentService
{
    public Amplitude $amplitude;
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

        $set = [];
        $unset = [];
        foreach ($assignment->variants as $flagKey => $variant) {
            if (!$variant->key) {
                continue;
            }
            $event->eventProperties["{$flagKey}.variant"] = $variant->key;
            $version = $variant->metadata['flagVersion'] ?? null;
            $segmentName = $variant->metadata['segmentName'] ?? null;
            if ($version && $segmentName) {
                $event->eventProperties["{$flagKey}.details"] = "v{$version} rule:{$segmentName}";
            }
            $flagType = $variant->metadata['flagType'] ?? null;
            $default = $variant->metadata['default'] ?? false;
            if ($flagType == FLAG_TYPE_MUTUAL_EXCLUSION_GROUP) {
                continue;
            } elseif ($default) {
                $unset["[Experiment] {$flagKey}"] = '-';
            } else {
                $set["[Experiment] {$flagKey}"] = $variant->key;
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
