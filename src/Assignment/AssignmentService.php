<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;

const FLAG_TYPE_MUTUAL_EXCLUSION_GROUP = 'mutual-exclusion-group';
const DAY_MILLIS = 24 * 60 * 60 * 1000;

class AssignmentService
{
    private AssignmentTrackingProvider $assignmentTrackingProvider;
    private AssignmentFilterInterface $assignmentFilter;
    private string $apiKey;
    private int $minIdLength;

    public function __construct(AssignmentTrackingProvider $assignmentTrackingProvider, AssignmentFilterInterface $assignmentFilter, string $apiKey = '', int $minIdLength = AssignmentConfig::DEFAULTS['minIdLength'])
    {
        $this->assignmentTrackingProvider = $assignmentTrackingProvider;
        $this->assignmentFilter = $assignmentFilter;
        $this->apiKey = $apiKey;
        $this->minIdLength = $minIdLength;
    }

    public function track(Assignment $assignment): void
    {
        if ($this->assignmentFilter->shouldTrack($assignment)) {
            $this->assignmentTrackingProvider->track($assignment);
        }
    }

    /**
     * @param array<Variant> $variants
     */
    public function createAssignment(User $user, array $variants): Assignment
    {
        return new Assignment($user, $variants, $this->apiKey, $this->minIdLength);
    }
}
