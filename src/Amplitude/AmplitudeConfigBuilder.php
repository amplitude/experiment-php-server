<?php

namespace AmplitudeExperiment\Amplitude;

class AmplitudeConfigBuilder
{
    protected int $flushQueueSize = AmplitudeConfig::DEFAULTS['flushQueueSize'];
    protected int $flushMaxRetries = AmplitudeConfig::DEFAULTS['flushMaxRetries'];
    protected string $serverZone = AmplitudeConfig::DEFAULTS['serverZone'];
    protected ?string $serverUrl = null;
    protected bool $useBatch = AmplitudeConfig::DEFAULTS['useBatch'];

    public function __construct()
    {
    }

    public function flushQueueSize(int $flushQueueSize): AmplitudeConfigBuilder
    {
        $this->flushQueueSize = $flushQueueSize;
        return $this;
    }

    public function flushMaxRetries(int $flushMaxRetries): AmplitudeConfigBuilder
    {
        $this->flushMaxRetries = $flushMaxRetries;
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
            $this->flushMaxRetries,
            $this->serverZone,
            $this->serverUrl,
            $this->useBatch
        );
    }
}
