<?php

namespace AmplitudeExperiment\Assignment;
require_once __DIR__ . '/AssignmentService.php';

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class DefaultAssignmentFilter implements AssignmentFilter
{
    private ArrayAdapter $cache;

    public function __construct(ArrayAdapter $cache)
    {
        $this->cache = $cache;
    }

    public function shouldTrack(Assignment $assignment): bool
    {
        if (count($assignment->variants) === 0) {
            return false;
        }

        $canonicalAssignment = $assignment->canonicalize();

        try {
            $track = $this->cache->getItem($canonicalAssignment)->isHit();
        } catch (InvalidArgumentException $e) {
            $track = true;
        }

        if ($track) {
            try {
                $item = $this->cache->getItem($canonicalAssignment);
                $item->set(0);
                $this->cache->save($item);
            } catch (InvalidArgumentException $e) {
                // Ignore
            }
        }

        return $track;
    }
}
