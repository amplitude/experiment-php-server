<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\Event;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\Logger\LogLevel;
use AmplitudeExperiment\Test\Util\MockGuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class AmplitudeTest extends TestCase
{
    const API_KEY = 'test';

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
        $config = AmplitudeConfig::builder()
            ->serverUrl('test')
            ->useBatch(true)
            ->build();
        $this->assertEquals('test', $config->serverUrl);
    }

    public function testEmptyQueueAfterFlushSuccess()
    {
        $client = new MockAmplitude(self::API_KEY);
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new MockGuzzleHttpClient([], $handlerStack);
        $client->setHttpClient($httpClient);
        $event1 = new Event('test1');
        $event2 = new Event('test2');
        $event3 = new Event('test3');
        $client->logEvent($event1);
        $client->logEvent($event2);
        $client->logEvent($event3);
        $this->assertEquals(3, $client->getQueueSize());
        $client->flush();
        $this->assertEquals(0, $client->getQueueSize());
    }

    public function testFlushAfterMaxQueue()
    {
        // Initialize the request counter
        $requestCounter = 0;

        $config = AmplitudeConfig::builder()
            ->flushQueueSize(3)
            ->build();
        $client = new MockAmplitude(self::API_KEY, $config);
        $mockHandler = new MockHandler([
            function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;

                return new Response(200, ['X-Foo' => 'Bar']);
            },
        ]);

        // Create a handler stack with the mock handler
        $handlerStack = HandlerStack::create($mockHandler);

        // Create an instance of GuzzleFetchClient with the custom handler stack
        $httpClient = new MockGuzzleHttpClient([], $handlerStack);
        $client->setHttpClient($httpClient);
        $event1 = new Event('test1');
        $event2 = new Event('test2');
        $event3 = new Event('test3');
        $client->logEvent($event1);
        $client->logEvent($event2);
        $this->assertEquals(2, $client->getQueueSize());
        $client->logEvent($event3);
        $this->assertEquals(1, $requestCounter);
        $this->assertEquals(0, $client->getQueueSize());
    }

    public function testBackoffRetriesToFailure()
    {
        // Initialize the request counter
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()->build();
        $client = new MockAmplitude(self::API_KEY, $config);

        // Set up the mock handler with request counter incrementation logic
        $mockHandler = new MockHandler(array_fill(1, 5, function (RequestInterface $request, array $options) use (&$requestCounter) {
            $requestCounter++;
            return new RequestException('Error Communicating with Server', $request);
        }));

        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new MockGuzzleHttpClient(['retries' => 4], $handlerStack);
        $client->setHttpClient($httpClient);

        $event1 = new Event('test');
        $event1->userId = 'user_id';
        $client->logEvent($event1);
        $client->flush();

        // Assert the number of requests sent (including retries)
        $this->assertEquals(5, $requestCounter);
        $this->assertEquals(1, $client->getQueueSize());
    }


    public function testBackoffRetriesThenSuccess()
    {
        // Initialize the request counter
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()->build();
        $client = new MockAmplitude(self::API_KEY, $config);

        // Set up the mock handler with request counter incrementation logic
        $mockHandler = new MockHandler(array_fill(1, 2, function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;
                return new RequestException('Error Communicating with Server', $request);
            }) + [
                function (RequestInterface $request, array $options) use (&$requestCounter) {
                    $requestCounter++;

                    return new Response(200, ['X-Foo' => 'Bar']);
                },
            ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new MockGuzzleHttpClient(['retries' => 4], $handlerStack);
        $client->setHttpClient($httpClient);
        $event1 = new Event('test');
        $event1->userId = 'user_id';
        $client->logEvent($event1);
        $client->flush();
        $this->assertEquals(3, $requestCounter);
        $this->assertEquals(0, $client->getQueueSize());
    }
}
