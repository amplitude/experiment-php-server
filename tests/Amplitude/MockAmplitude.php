<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use Psr\Http\Client\ClientInterface;

class MockAmplitude extends Amplitude
{
    public function __construct(string $apiKey, ?AmplitudeConfig $config = null)
    {
        parent::__construct($apiKey, $config);
    }
    public function setHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
    public function __destruct()
    {
        // Do nothing
    }
    public function getQueueSize(): int
    {
        return count($this->queue);
    }
}
