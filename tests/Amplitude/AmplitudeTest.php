<?php

namespace Amplitude;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\Event;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Test\TestCase;

class AmplitudeTest extends TestCase
{
    const API_KEY = 'a6dd847b9d2f03c816d4f3f8458cdc1d';

    public function testAmplitudeConfigServerUrl() {
        $config = AmplitudeConfig::builder()
            ->build();
        $this->assertEquals('https://api2.amplitude.com/2/httpapi', $config->serverUrl);
        $config = AmplitudeConfig::builder()
            ->useBatch(true)
            ->build();
        $this->assertEquals('https://api2.amplitude.com/batch', $config->serverUrl);
        $config = AmplitudeConfig::builder()
            ->serverZone('EU')
            ->build();
        $this->assertEquals('https://api.eu.amplitude.com/2/httpapi', $config->serverUrl);
        $config = AmplitudeConfig::builder()
            ->serverZone('EU')
            ->useBatch(true)
            ->build();
        $this->assertEquals('https://api.eu.amplitude.com/batch', $config->serverUrl);
    }

    /**
     * @throws GuzzleException
     */
    public function testAmplitude()
    {
        $client = new Amplitude(self::API_KEY, true);
        $event1 = new Event('test1');
        $event1->userId = 'tim.yiu@amplitude.com';
        $client->logEvent($event1);
        $client->flush()->wait();
        $this->assertTrue(true);
    }
}
