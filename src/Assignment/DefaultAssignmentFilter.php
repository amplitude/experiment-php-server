<?php

namespace AmplitudeExperiment\Assignment;
require_once __DIR__ . '/AssignmentService.php';

class DefaultAssignmentFilter implements AssignmentFilter
{
    private LRUCache $cache;

    public function __construct(int $size)
    {
        $this->cache = new LRUCache($size, $ttlMillis);
    }

    public function shouldTrack(Assignment $assignment): bool
    {
        if (count($assignment->variants) === 0) {
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
