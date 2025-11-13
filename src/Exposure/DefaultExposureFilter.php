<?php

namespace AmplitudeExperiment\Exposure;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Default implementation of ExposureFilterInterface.
 */
class DefaultExposureFilter implements ExposureFilterInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function shouldTrack(Exposure $exposure): bool
    {
        if (count($exposure->variants) === 0) {
            // Don't track empty exposures.
            return false;
        }

        $canonicalExposure = $exposure->canonicalize();

        try {
            $item = $this->cache->getItem($canonicalExposure);
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

