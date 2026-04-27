<?php

namespace AmplitudeExperiment\Test\Remote;

use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Http\RetryConfig;
use AmplitudeExperiment\Http\RetryingClient;
use AmplitudeExperiment\Remote\FetchOptions;
use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;
use AmplitudeExperiment\Test\Util\MockPsr18Client;
use AmplitudeExperiment\User;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RemoteEvaluationClientTest extends TestCase
{
    private string $apiKey = 'server-qz35UwzJ5akieoAdIgzM4m9MIiOLXLoz';
    private User $testUser;

    public function __construct()
    {
        parent::__construct();
        $this->testUser = User::builder()
            ->userId('test_user')
            ->build();
    }

    public function testFetchSuccess(): void
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testFetchWithNoRetriesSurfacesTransportFailure(): void
    {
        // Single attempt, no retry — a transport error short-circuits to empty result.
        $mock = new MockPsr18Client([
            function (RequestInterface $request) {
                return new ConnectException('Simulated transport failure', $request);
            },
        ]);
        $config = RemoteEvaluationConfig::builder()
            ->httpClient(new RetryingClient($mock, new RetryConfig(1, 0, 0, 1.0)))
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);
        $variants = $client->fetch($this->testUser);
        $this->assertEquals([], $variants);
    }

    public function testRetrySucceedsAfterTransientFailure(): void
    {
        $requestCounter = 0;
        $mock = new MockPsr18Client([
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new ConnectException('Error Communicating with Server', $request);
            },
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
            },
        ]);
        $retryConfig = new RetryConfig(2, 100, 500, 2.0);

        $config = RemoteEvaluationConfig::builder()
            ->httpClient(new RetryingClient($mock, $retryConfig))
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);

        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
        $this->assertEquals(2, $requestCounter);
    }

    public function testRetrySucceedsWithZeroBackoff(): void
    {
        $requestCounter = 0;
        $mock = new MockPsr18Client([
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new ConnectException('Error Communicating with Server', $request);
            },
            function (RequestInterface $request) use (&$requestCounter) {
                $requestCounter++;
                return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
            },
        ]);
        $retryConfig = new RetryConfig(2, 0, 0, 1.0);

        $config = RemoteEvaluationConfig::builder()
            ->httpClient(new RetryingClient($mock, $retryConfig))
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);

        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
        $this->assertEquals(2, $requestCounter);
    }

    public function testFetchWithFlagKeysOptionsSuccess(): void
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser, ['sdk-ci-test']);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testExperimentInitializeRemote(): void
    {
        $experiment = new Experiment();
        $client = $experiment->initializeRemote($this->apiKey);
        $this->assertEquals($client, $experiment->initializeRemote($this->apiKey));
    }

    public function testFetchWithFetchOptionsSuccess(): void
    {
        $assertExpectedHeaders = function (RequestInterface $request, ?string $expectedTrack, ?string $expectedExposure): Response {
            $headers = $request->getHeaders();
            $this->assertEquals(base64_encode('["sdk-ci-test"]'), $headers['X-Amp-Exp-Flag-Keys'][0]);
            if ($expectedTrack === null) {
                $this->assertArrayNotHasKey('X-Amp-Exp-Track', $headers);
            } else {
                $this->assertEquals($expectedTrack, $headers['X-Amp-Exp-Track'][0]);
            }
            if ($expectedExposure === null) {
                $this->assertArrayNotHasKey('X-Amp-Exp-Exposure-Track', $headers);
            } else {
                $this->assertEquals($expectedExposure, $headers['X-Amp-Exp-Exposure-Track'][0]);
            }
            return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
        };

        $mock = new MockPsr18Client([
            function (RequestInterface $request) use ($assertExpectedHeaders) {
                return $assertExpectedHeaders($request, 'track', 'track');
            },
            function (RequestInterface $request) use ($assertExpectedHeaders) {
                return $assertExpectedHeaders($request, 'no-track', 'no-track');
            },
            function (RequestInterface $request) use ($assertExpectedHeaders) {
                return $assertExpectedHeaders($request, null, null);
            },
            function (RequestInterface $request) use ($assertExpectedHeaders) {
                return $assertExpectedHeaders($request, null, null);
            },
            function (RequestInterface $request) {
                $headers = $request->getHeaders();
                $this->assertArrayNotHasKey('X-Amp-Exp-Flag-Keys', $headers);
                $this->assertArrayNotHasKey('X-Amp-Exp-Track', $headers);
                $this->assertArrayNotHasKey('X-Amp-Exp-Exposure-Track', $headers);
                return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
            },
        ]);

        $config = RemoteEvaluationConfig::builder()->httpClient($mock)->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);

        $variants = $client->fetch($this->testUser, new FetchOptions(['sdk-ci-test'], true, true));
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variants['sdk-ci-test']->key);

        $variants = $client->fetch($this->testUser, new FetchOptions(['sdk-ci-test'], false, false));
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variants['sdk-ci-test']->key);

        $variants = $client->fetch($this->testUser, new FetchOptions(['sdk-ci-test']));
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variants['sdk-ci-test']->key);

        $variants = $client->fetch($this->testUser, ['sdk-ci-test']);
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variants['sdk-ci-test']->key);

        $variants = $client->fetch($this->testUser);
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variants['sdk-ci-test']->key);
    }
}
