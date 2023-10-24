<?php

namespace AmplitudeExperiment;

use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;
use AmplitudeExperiment\Remote\RemoteEvaluationConfigBuilder;

class Experiment
{
    private array $remoteInstances = [];

    /**
     * Initializes a [RemoteEvaluationClient] instance. If a RemoteEvaluationClient instance has already been
     * initialized with the same apiKey, the existing instance will be returned.
     *
     * @param string $apiKey apiKey The API key. This can be found in the Experiment settings and should not
     * be null or empty.
     * @param ?RemoteEvaluationConfig $config config see {@link RemoteEvaluationConfig} for configuration options
     */
    public function initializeRemote(string $apiKey, ?RemoteEvaluationConfig $config = null): RemoteEvaluationClient
    {
        if (!isset($this->remoteInstances[$apiKey])) {
            $config = $config ?? RemoteEvaluationConfig::builder()->build();
            $this->remoteInstances[$apiKey] = new RemoteEvaluationClient($apiKey, $config);
        }
        return $this->remoteInstances[$apiKey];
    }
}
