<?php

namespace AmplitudeExperiment\Assignment;

use Psr\Cache\CacheItemPoolInterface;

interface AssignmentFilter
{
    /**
     * Constructor.
     *
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache);

    /**
     * Determine if an assignment should be tracked.
     *
     * @param Assignment $assignment
     * @return bool
     */
    public function shouldTrack(Assignment $assignment): bool;
}
