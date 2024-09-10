<?php

namespace AmplitudeExperiment\Assignment;
require_once __DIR__ . '/AssignmentService.php';

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Default implementation of AssignmentFilterInterface.
 */
class DefaultAssignmentFilter implements AssignmentFilterInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
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
            $item = $this->cache->getItem($canonicalAssignment);
            $track = !$item->isHit();
            if ($track) {
                $item->set(0);
                $this->cache->save($item);
            }
        } catch (InvalidArgumentException $e) {
            $track = true;
        }

        return $track;
    }
}
