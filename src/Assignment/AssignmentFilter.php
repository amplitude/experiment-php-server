<?php

namespace AmplitudeExperiment\Assignment;

use Psr\Cache\CacheItemPoolInterface;

interface AssignmentFilter
{

    /**
     * Determine if an assignment should be tracked.
     *
     * @param Assignment $assignment
     * @return bool
     */
    public function shouldTrack(Assignment $assignment): bool;
}
