<?php

namespace AmplitudeExperiment\Exposure;

class ExposureConfigBuilder
{
    protected int $cacheCapacity = ExposureConfig::DEFAULTS['cacheCapacity'];
    protected ?ExposureTrackingProvider $exposureTrackingProvider;
    protected ?string $apiKey;
    protected ?ExposureFilterInterface $exposureFilter = null;

    public function __construct(?string $apiKey = null, ?ExposureTrackingProvider $exposureTrackingProvider = null)
    {
        $this->apiKey = $apiKey;
        $this->exposureTrackingProvider = $exposureTrackingProvider;
    }

    public function cacheCapacity(int $cacheCapacity): ExposureConfigBuilder
    {
        $this->cacheCapacity = $cacheCapacity;
        return $this;
    }

    public function exposureFilter(ExposureFilterInterface $exposureFilter): ExposureConfigBuilder
    {
        $this->exposureFilter = $exposureFilter;
        return $this;
    }

    public function build(): ExposureConfig
    {
        return new ExposureConfig(
            $this->apiKey,
            $this->cacheCapacity,
            $this->exposureTrackingProvider,
            $this->exposureFilter
        );
    }
}

