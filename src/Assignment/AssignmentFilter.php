<?php

namespace AmplitudeExperiment\Assignment;
require_once __DIR__ . '/AssignmentService.php';

class AssignmentFilter
{
    private LRUCache $cache;

    public function __construct(int $size, int $ttlMillis = DAY_MILLIS)
    {
        $this->cache = new LRUCache($size, $ttlMillis);
    }

    public function shouldTrack(Assignment $assignment): bool
    {
        if (count($assignment->results) === 0) {
            return false;
        }

        $canonicalAssignment = $assignment->canonicalize();
        $track = $this->cache->get($canonicalAssignment) === null;

        if ($track) {
            $this->cache->put($canonicalAssignment, 0);
        }

        return $track;
    }
}
