<?php

namespace AmplitudeExperiment;

use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;

class Experiment
{
    /**
     * @var array<string, RemoteEvaluationClient>
     */
    private array $remoteInstances = [];
    /**
     * @var array<string, LocalEvaluationClient>
     */
    private array $localInstances = [];

    /**
     * Initializes a {@link RemoteEvaluationClient} instance. If a RemoteEvaluationClient instance has already been
     * initialized with the same apiKey, the existing instance will be returned.
     *
     * @param $apiKey string The API key. This can be found in the Experiment settings and should not
     * be null or empty.
     * @param $config ?RemoteEvaluationConfig see {@link RemoteEvaluationConfig} for configuration options
     * @return RemoteEvaluationClient
     */
    public function initializeRemote(string $apiKey, ?RemoteEvaluationConfig $config = null): RemoteEvaluationClient
    {
        if (!isset($this->remoteInstances[$apiKey])) {
            $config = $config ?? RemoteEvaluationConfig::builder()->build();
            $this->remoteInstances[$apiKey] = new RemoteEvaluationClient($apiKey, $config);
        }
        return $this->remoteInstances[$apiKey];
    }

    /**
     * Initializes a {@link LocalEvaluationClient} instance. If a LocalEvaluationClient instance has already been
     * initialized with the same apiKey, the existing instance will be returned.
     *
     * @param $apiKey string The API key. This can be found in the Experiment settings and should not
     * be null or empty.
     * @param $config ?LocalEvaluationConfig see {@link LocalEvaluationConfig} for configuration options
     * @return LocalEvaluationClient
     */
    public function initializeLocal(string $apiKey, ?LocalEvaluationConfig $config = null): LocalEvaluationClient
    {
        if (!isset($this->localInstances[$apiKey])) {
            $config = $config ?? LocalEvaluationConfig::builder()->build();
            $this->localInstances[$apiKey] = new LocalEvaluationClient($apiKey, $config);
        }
        return $this->localInstances[$apiKey];
    }

}
