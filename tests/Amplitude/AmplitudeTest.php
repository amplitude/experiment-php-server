<?php

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Amplitude\Event;
use AmplitudeExperiment\Http\RetryConfig;
use AmplitudeExperiment\Http\RetryingClient;
use AmplitudeExperiment\Test\Util\MockPsr18Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class AmplitudeTest extends TestCase
{
    const API_KEY = 'test';

    public function testAmplitudeConfigServerUrl(): void
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

    public function testEmptyQueueAfterFlushSuccess(): void
    {
        $client = new MockAmplitude(self::API_KEY);
        $client->setHttpClient(new MockPsr18Client([new Response(200, ['X-Foo' => 'Bar'])]));

        $client->logEvent(new Event('test1'));
        $client->logEvent(new Event('test2'));
        $client->logEvent(new Event('test3'));
        $this->assertEquals(3, $client->getQueueSize());
        $client->flush();
        $this->assertEquals(0, $client->getQueueSize());
    }

    public function testFlushAfterMaxQueue(): void
    {
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()
            ->flushQueueSize(3)
            ->build();
        $client = new MockAmplitude(self::API_KEY, $config);
        $mock = new MockPsr18Client([
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new Response(200, ['X-Foo' => 'Bar']);
            },
        ]);
        $client->setHttpClient($mock);

        $client->logEvent(new Event('test1'));
        $client->logEvent(new Event('test2'));
        $this->assertEquals(2, $client->getQueueSize());
        $client->logEvent(new Event('test3'));
        $this->assertEquals(1, $requestCounter);
        $this->assertEquals(0, $client->getQueueSize());
    }

    public function testBackoffRetriesToFailureWhenPostRetryEnabled(): void
    {
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()->build();
        $client = new MockAmplitude(self::API_KEY, $config);

        $mock = new MockPsr18Client(array_fill(0, 5, function (RequestInterface $request) use (&$requestCounter) {
            $requestCounter++;
            return $this->connectError($request);
        }));
        $retryConfig = new RetryConfig(5, 0, 0, 1.0, ['POST']);
        $client->setHttpClient(new RetryingClient($mock, $retryConfig));

        $event = new Event('test');
        $event->userId = 'user_id';
        $client->logEvent($event);
        $client->flush();

        $this->assertEquals(5, $requestCounter);
        $this->assertEquals(1, $client->getQueueSize());
    }

    public function testBackoffRetriesThenSuccessWhenPostRetryEnabled(): void
    {
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()->build();
        $client = new MockAmplitude(self::API_KEY, $config);

        $mock = new MockPsr18Client([
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return $this->connectError($request);
            },
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return $this->connectError($request);
            },
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new Response(200, ['X-Foo' => 'Bar']);
            },
        ]);
        $retryConfig = new RetryConfig(5, 0, 0, 1.0, ['POST']);
        $client->setHttpClient(new RetryingClient($mock, $retryConfig));

        $event = new Event('test');
        $event->userId = 'user_id';
        $client->logEvent($event);
        $client->flush();

        $this->assertEquals(3, $requestCounter);
        $this->assertEquals(0, $client->getQueueSize());
    }

    public function testPostNotRetriedByDefaultRetryConfig(): void
    {
        $requestCounter = 0;
        $config = AmplitudeConfig::builder()->build();
        $client = new MockAmplitude(self::API_KEY, $config);

        $mock = new MockPsr18Client(array_fill(0, 3, function (RequestInterface $request) use (&$requestCounter) {
            $requestCounter++;
            return $this->connectError($request);
        }));
        // Default RetryConfig retries GET only — POST passes through with no retry.
        $client->setHttpClient(new RetryingClient($mock, new RetryConfig(5, 0, 0, 1.0)));

        $event = new Event('test');
        $event->userId = 'user_id';
        $client->logEvent($event);
        $client->flush();

        $this->assertEquals(1, $requestCounter);
        $this->assertEquals(1, $client->getQueueSize());
    }

    private function connectError(RequestInterface $request): ConnectException
    {
        return new ConnectException('Error Communicating with Server', $request);
    }
}
