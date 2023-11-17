<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\Event;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Monolog\Test\TestCase;

class AmplitudeTest extends TestCase
{
    private array $historyContainer;
    const API_KEY = 'a6dd847b9d2f03c816d4f3f8458cdc1d';

    public function setUp(): void
    {
        $this->historyContainer = [];
    }

    public function testAmplitudeConfigServerUrl()
    {
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

    public function testAmplitude()
    {
        $client = new MockAmplitude(self::API_KEY, true);
        $event1 = new Event('test1');
        $event1->userId = 'tim.yiu@amplitude.com';
        $client->logEvent($event1);
        $client->flush()->wait();
        $this->assertTrue(true);
    }

    public function testBackoffRetriesToFailure()
    {
        $config = AmplitudeConfig::builder()
            ->flushMaxRetries(5)
            ->build();
        $client = new MockAmplitude(self::API_KEY, true, $config);
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack,
            'on_stats' => function (TransferStats $stats) {
            $this->historyContainer[] = $stats;
        },]);
        $client->setHttpClient($httpClient);
        $event1 = new Event('test');
        $event1->userId = 'user_id';
        $client->logEvent($event1);
        $client->flush()->wait();
        $this->assertEquals(5, $this->countPostRequests());
    }

    public function testBackoffRetriesThenSuccess()
    {
        $config = AmplitudeConfig::builder()
            ->flushMaxRetries(5)
            ->build();
        $client = new MockAmplitude(self::API_KEY, true, $config);
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new RequestException('Error Communicating with Server', new Request('POST', 'test')),
            new Response(200, ['X-Foo' => 'Bar']),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack,
            'on_stats' => function (TransferStats $stats) {
                $this->historyContainer[] = $stats;
            },]);
        $client->setHttpClient($httpClient);
        $event1 = new Event('test');
        $event1->userId = 'user_id';
        $client->logEvent($event1);
        $client->flush()->wait();
        $this->assertEquals(3, $this->countPostRequests());
    }

    private function countPostRequests(): int
    {
        return count(array_filter($this->historyContainer, function (TransferStats $stats) {
            return $stats->getRequest()->getMethod() === 'POST';
        }));
    }
}
