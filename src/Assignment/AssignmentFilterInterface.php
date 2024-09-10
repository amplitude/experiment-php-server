<?php

namespace AmplitudeExperiment\Assignment;

interface AssignmentFilterInterface
{

    /**
     * Determine if an assignment should be tracked.
     *
     * @param Assignment $assignment
     * @return bool
     */
    public function shouldTrack(Assignment $assignment): bool;
}
