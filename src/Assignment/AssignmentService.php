<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;

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
