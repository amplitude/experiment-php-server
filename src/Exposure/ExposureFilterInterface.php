<?php

namespace AmplitudeExperiment\Exposure;

/**
 * Interface for exposure filter set in {@link ExposureConfig}, used to determine whether an exposure should be tracked.
 */
interface ExposureFilterInterface
{
    /**
     * Determine if an exposure should be tracked.
     *
     * @param Exposure $exposure
     * @return bool
     */
    public function shouldTrack(Exposure $exposure): bool;
}

