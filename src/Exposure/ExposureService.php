<?php

namespace AmplitudeExperiment\Exposure;

class ExposureService
{
    private ExposureTrackingProvider $exposureTrackingProvider;
    private ExposureFilterInterface $exposureFilter;

    public function __construct(ExposureTrackingProvider $exposureTrackingProvider, ExposureFilterInterface $exposureFilter)
    {
        $this->exposureTrackingProvider = $exposureTrackingProvider;
        $this->exposureFilter = $exposureFilter;
    }

    public function track(Exposure $exposure): void
    {
        if ($this->exposureFilter->shouldTrack($exposure)) {
            $this->exposureTrackingProvider->track($exposure);
        }
    }
}

