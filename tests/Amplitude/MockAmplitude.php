<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;

class MockAmplitude extends Amplitude
{
    public function __construct(string $apiKey, AmplitudeConfig $config = null)
    {
        parent::__construct($apiKey, $config);
    }
    public function setHttpClient(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }
    public function __destruct() {
        // Do nothing
    }
    public function getQueueSize() : int {
        return count($this->queue);
    }
}
