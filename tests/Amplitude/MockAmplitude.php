<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use GuzzleHttp\Client;

class MockAmplitude extends Amplitude
{
    public function __construct(string $apiKey, bool $debug, AmplitudeConfig $config = null)
    {
        parent::__construct($apiKey, $debug, $config);
    }
    public function setHttpClient(Client $httpClient) {
        $this->httpClient = $httpClient;
    }
    public function getQueueSize() : int {
        return count($this->queue);
    }
}
