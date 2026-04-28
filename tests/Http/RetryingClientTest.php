<?php

namespace AmplitudeExperiment\Test\Http;

use AmplitudeExperiment\Http\RetryConfig;
use AmplitudeExperiment\Http\RetryingClient;
use AmplitudeExperiment\Test\Util\MockPsr18Client;
use AmplitudeExperiment\Test\Util\Psr7TestUtil;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class RetryingClientTest extends TestCase
{
    public function testSucceedsOnFirstAttemptWithoutRetry(): void
    {
        $mock = new MockPsr18Client([Psr7TestUtil::response(200)]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(Psr7TestUtil::request('GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $mock->callCount);
    }

    public function testRetriesUntilSuccessWithinBudget(): void
    {
        $mock = new MockPsr18Client([
            Psr7TestUtil::clientException(),
            Psr7TestUtil::clientException(),
            Psr7TestUtil::response(200),
        ]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(Psr7TestUtil::request('GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(3, $mock->callCount);
    }

    public function testThrowsLastExceptionWhenBudgetExhausted(): void
    {
        $queue = [];
        for ($i = 0; $i < 4; $i++) {
            $queue[] = Psr7TestUtil::clientException();
        }
        $mock = new MockPsr18Client($queue);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 4]));

        try {
            $client->sendRequest(Psr7TestUtil::request('GET'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(4, $mock->callCount);
        }
    }

    public function testPostNotRetriedByDefault(): void
    {
        $mock = new MockPsr18Client([Psr7TestUtil::clientException()]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        try {
            $client->sendRequest(Psr7TestUtil::request('POST'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testPostRetriedWhenInRetryMethods(): void
    {
        $mock = new MockPsr18Client([
            Psr7TestUtil::clientException(),
            Psr7TestUtil::response(200),
        ]);
        $client = new RetryingClient($mock, $this->fastConfig([
            'attempts' => 3,
            'retryMethods' => ['GET', 'POST'],
        ]));

        $response = $client->sendRequest(Psr7TestUtil::request('POST'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $mock->callCount);
    }

    public function testNonPsrExceptionPropagatesWithoutRetry(): void
    {
        $mock = new MockPsr18Client([new RuntimeException('not a PSR-18 exception')]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        try {
            $client->sendRequest(Psr7TestUtil::request('GET'));
            $this->fail('expected RuntimeException to be thrown');
        } catch (RuntimeException $e) {
            $this->assertSame('not a PSR-18 exception', $e->getMessage());
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testHttpErrorResponsesNotRetried(): void
    {
        $mock = new MockPsr18Client([Psr7TestUtil::response(503)]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(Psr7TestUtil::request('GET'));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(1, $mock->callCount);
    }

    public function testSingleAttemptDisablesRetry(): void
    {
        $mock = new MockPsr18Client([Psr7TestUtil::clientException()]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 1]));

        try {
            $client->sendRequest(Psr7TestUtil::request('GET'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testMethodMatchIsCaseInsensitive(): void
    {
        $mock = new MockPsr18Client([
            Psr7TestUtil::clientException(),
            Psr7TestUtil::response(200),
        ]);
        // retryMethods passed lowercase; constructor normalizes to uppercase
        $client = new RetryingClient($mock, $this->fastConfig([
            'attempts' => 3,
            'retryMethods' => ['get'],
        ]));

        $response = $client->sendRequest(Psr7TestUtil::request('GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $mock->callCount);
    }

    public function testRetryConfigRejectsZeroAttempts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryConfig(0);
    }

    public function testRetryConfigRejectsInvalidBackoffBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryConfig(3, 5000, 1000);
    }

    public function testRetryConfigRejectsScalarBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryConfig(3, 100, 1000, 0.5);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function fastConfig(array $overrides = []): RetryConfig
    {
        $defaults = [
            'attempts' => 3,
            'backoffMinMillis' => 0,
            'backoffMaxMillis' => 0,
            'backoffScalar' => 1.0,
            'retryMethods' => ['GET'],
        ];
        $merged = array_merge($defaults, $overrides);
        return new RetryConfig(
            $merged['attempts'],
            $merged['backoffMinMillis'],
            $merged['backoffMaxMillis'],
            $merged['backoffScalar'],
            $merged['retryMethods']
        );
    }
}
