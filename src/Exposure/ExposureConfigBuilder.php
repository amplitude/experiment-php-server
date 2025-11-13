<?php

namespace AmplitudeExperiment\Exposure;

use Symfony\Component\Cache\Adapter\ArrayAdapter;

use const AmplitudeExperiment\Assignment\DAY_MILLIS;

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
        if ($this->exposureFilter === null) {
            $this->exposureFilter = new DefaultExposureFilter(new ArrayAdapter(DAY_MILLIS / 1000, false, 0, $this->cacheCapacity));
        }
        return new ExposureConfig(
            $this->apiKey,
            $this->cacheCapacity,
            $this->exposureTrackingProvider,
            $this->exposureFilter
        );
    }
}

