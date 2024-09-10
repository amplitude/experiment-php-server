<?php

namespace AmplitudeExperiment\Assignment;

interface AssignmentFilterInterface
    /**
     * Interface for assignment filter set in {@link AssignmentConfig}, used to determine whether an assignment should be tracked.
     */
{

    /**
     * Determine if an assignment should be tracked.
     *
     * @param Assignment $assignment
     * @return bool
     */
    public function shouldTrack(Assignment $assignment): bool;
}
