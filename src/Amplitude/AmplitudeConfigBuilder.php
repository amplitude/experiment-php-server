<?php

namespace AmplitudeExperiment\Amplitude;

use AmplitudeExperiment\Http\FetchClientInterface;

class AmplitudeConfigBuilder
{
    protected int $flushQueueSize = AmplitudeConfig::DEFAULTS['flushQueueSize'];
    protected int $minIdLength = AmplitudeConfig::DEFAULTS['minIdLength'];
    protected string $serverZone = AmplitudeConfig::DEFAULTS['serverZone'];
    protected ?string $serverUrl = null;
    protected bool $useBatch = AmplitudeConfig::DEFAULTS['useBatch'];
    protected ?FetchClientInterface $fetchClient = AmplitudeConfig::DEFAULTS['fetchClient'];
    protected array $guzzleClientConfig = AmplitudeConfig::DEFAULTS['guzzleClientConfig'];

    public function __construct()
    {
    }

    public function flushQueueSize(int $flushQueueSize): AmplitudeConfigBuilder
    {
        $this->flushQueueSize = $flushQueueSize;
        return $this;
    }

    public function minIdLength(int $minIdLength): AmplitudeConfigBuilder
    {
        $this->minIdLength = $minIdLength;
        return $this;
    }

    public function serverZone(string $serverZone): AmplitudeConfigBuilder
    {
        $this->serverZone = $serverZone;
        return $this;
    }

    public function serverUrl(string $serverUrl): AmplitudeConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function useBatch(bool $useBatch): AmplitudeConfigBuilder
    {
        $this->useBatch = $useBatch;
        return $this;
    }

    public function fetchClient(FetchClientInterface $fetchClient): AmplitudeConfigBuilder
    {
        $this->fetchClient = $fetchClient;
        return $this;
    }

    public function guzzleClientConfig(array $guzzleClientConfig): AmplitudeConfigBuilder
    {
        $this->guzzleClientConfig = $guzzleClientConfig;
        return $this;
    }

    public function build()
    {
        if (!$this->serverUrl) {
            if ($this->useBatch) {
                $this->serverUrl = AmplitudeConfig::DEFAULTS['serverUrl'][$this->serverZone]['batch'];
            } else {
                $this->serverUrl = AmplitudeConfig::DEFAULTS['serverUrl'][$this->serverZone]['v2'];
            }
        }
        return new AmplitudeConfig(
            $this->flushQueueSize,
            $this->minIdLength,
            $this->serverZone,
            $this->serverUrl,
            $this->useBatch,
            $this->fetchClient,
            $this->guzzleClientConfig
        );
    }
}
