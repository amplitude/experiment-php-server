<?php

namespace AmplitudeExperiment\Exposure;

/**
 * Configuration options for exposure tracking. This is an object that can be created using
 * a {@link ExposureConfigBuilder}. Example usage:
 *
 * ```
 * ExposureConfig::builder('api-key')->cacheCapacity(1000)->build();
 * ```
 */
class ExposureConfig
{
    /**
     * The Amplitude Project API key. If not provided, the deployment key will be used.
     */
    public ?string $apiKey;
    /**
     * The maximum number of exposures stored in the exposure cache if a {@link DefaultExposureFilter} is used.
     */
    public int $cacheCapacity;
    /**
     * The provider for tracking exposure events to Amplitude
     */
    public ?ExposureTrackingProvider $exposureTrackingProvider;
    /**
     * The filter used to determine whether an Exposure event should be tracked.
     * Default to {@link DefaultExposureFilter}.
     */
    public ExposureFilterInterface $exposureFilter;

    const DEFAULTS = [
        'cacheCapacity' => 65536
    ];

    public function __construct(?string $apiKey, int $cacheCapacity, ?ExposureTrackingProvider $exposureTrackingProvider,
                                ExposureFilterInterface $exposureFilter)
    {
        $this->apiKey = $apiKey;
        $this->cacheCapacity = $cacheCapacity;
        $this->exposureTrackingProvider = $exposureTrackingProvider;
        $this->exposureFilter = $exposureFilter;
    }

    public static function builder(?string $apiKey = null, ?ExposureTrackingProvider $exposureTrackingProvider = null): ExposureConfigBuilder
    {
        return new ExposureConfigBuilder($apiKey, $exposureTrackingProvider);
    }
}

