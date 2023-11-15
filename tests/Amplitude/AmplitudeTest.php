<?php

namespace Amplitude;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\Event;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Test\TestCase;

class AmplitudeTest extends TestCase
{
    const API_KEY = 'a6dd847b9d2f03c816d4f3f8458cdc1d';

    /**
     * @throws GuzzleException
     */
    public function testAmplitude()
    {
        $client = new Amplitude(self::API_KEY);
        $event1 = new Event('test1');
        $event2 = new Event('test2');
        $event3 = new Event('test3');
        $event1->userId = 'tim.yiu@amplitude.com';
        $event2->userId = 'tim.yiu@amplitude.com';
        $event3->userId = 'tim.yiu@amplitude.com';
        $client->logEvent($event1);
        $client->logEvent($event2);
        $client->logEvent($event3);
        $client->flush();
        $this->assertTrue(true);
    }
}
