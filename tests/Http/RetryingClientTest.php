<?php

namespace AmplitudeExperiment\Test\Http;

use AmplitudeExperiment\Http\RetryConfig;
use AmplitudeExperiment\Http\RetryingClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class RetryingClientTest extends TestCase
{
    public function testSucceedsOnFirstAttemptWithoutRetry(): void
    {
        $mock = $this->mockClient([new Response(200)]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(new Request('GET', 'https://x'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $mock->callCount);
    }

    public function testRetriesUntilSuccessWithinBudget(): void
    {
        $mock = $this->mockClient([
            $this->networkError(),
            $this->networkError(),
            new Response(200),
        ]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(new Request('GET', 'https://x'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(3, $mock->callCount);
    }

    public function testThrowsLastExceptionWhenBudgetExhausted(): void
    {
        $mock = $this->mockClient(array_fill(0, 4, $this->networkError()));
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 4]));

        try {
            $client->sendRequest(new Request('GET', 'https://x'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(4, $mock->callCount);
        }
    }

    public function testPostNotRetriedByDefault(): void
    {
        $mock = $this->mockClient([$this->networkError()]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        try {
            $client->sendRequest(new Request('POST', 'https://x'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testPostRetriedWhenInRetryMethods(): void
    {
        $mock = $this->mockClient([
            $this->networkError(),
            new Response(200),
        ]);
        $client = new RetryingClient($mock, $this->fastConfig([
            'attempts' => 3,
            'retryMethods' => ['GET', 'POST'],
        ]));

        $response = $client->sendRequest(new Request('POST', 'https://x'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $mock->callCount);
    }

    public function testNonPsrExceptionPropagatesWithoutRetry(): void
    {
        $mock = $this->mockClient([new RuntimeException('not a PSR-18 exception')]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        try {
            $client->sendRequest(new Request('GET', 'https://x'));
            $this->fail('expected RuntimeException to be thrown');
        } catch (RuntimeException $e) {
            $this->assertSame('not a PSR-18 exception', $e->getMessage());
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testHttpErrorResponsesNotRetried(): void
    {
        $mock = $this->mockClient([new Response(503)]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 5]));

        $response = $client->sendRequest(new Request('GET', 'https://x'));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(1, $mock->callCount);
    }

    public function testSingleAttemptDisablesRetry(): void
    {
        $mock = $this->mockClient([$this->networkError()]);
        $client = new RetryingClient($mock, $this->fastConfig(['attempts' => 1]));

        try {
            $client->sendRequest(new Request('GET', 'https://x'));
            $this->fail('expected ClientExceptionInterface to be thrown');
        } catch (ClientExceptionInterface $e) {
            $this->assertSame(1, $mock->callCount);
        }
    }

    public function testMethodMatchIsCaseInsensitive(): void
    {
        $mock = $this->mockClient([
            $this->networkError(),
            new Response(200),
        ]);
        // retryMethods passed lowercase; constructor normalizes to uppercase
        $client = new RetryingClient($mock, $this->fastConfig([
            'attempts' => 3,
            'retryMethods' => ['get'],
        ]));

        $response = $client->sendRequest(new Request('GET', 'https://x'));

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
     * @param array<int, ResponseInterface|\Throwable> $queue
     */
    private function mockClient(array $queue): MockHttpClient
    {
        return new MockHttpClient($queue);
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

    private function networkError(): ClientExceptionInterface
    {
        return new class extends RuntimeException implements ClientExceptionInterface {
        };
    }
}

/**
 * Minimal PSR-18 client that returns queued responses or throws queued
 * exceptions in order. Tracks call count for assertions.
 */
class MockHttpClient implements ClientInterface
{
    /** @var array<int, ResponseInterface|\Throwable> */
    private array $queue;
    public int $callCount = 0;

    /**
     * @param array<int, ResponseInterface|\Throwable> $queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->callCount++;
        if (empty($this->queue)) {
            throw new RuntimeException('MockHttpClient queue exhausted');
        }
        $next = array_shift($this->queue);
        if ($next instanceof \Throwable) {
            throw $next;
        }
        return $next;
    }
}
