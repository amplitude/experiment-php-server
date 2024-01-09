<?php

namespace AmplitudeExperiment\Test\Remote;

use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;
use AmplitudeExperiment\Test\Util\MockGuzzleHttpClient;
use AmplitudeExperiment\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
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

    public function testFetchSuccess()
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testFetchWithNoRetriesTimeoutFailure()
    {
        $guzzleConfig = ['retries' => 0, 'timeoutMillis' => 1];
        $config = RemoteEvaluationConfig::builder()
            ->guzzleClientConfig($guzzleConfig)
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);
        $variants = $client->fetch($this->testUser);
        $this->assertEquals([], $variants);
    }

    public function testFetchNoRetriesTimeoutFailureRetrySuccess()
    {
        // Initialize the request counter
        $requestCounter = 0;

        // Set up the mock handler
        $mockHandler = new MockHandler([
            // Simulate a failure (e.g., timeout) for the first request
            function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;

                return new RequestException('Error Communicating with Server', $request);
            },
            // Simulate a successful response for the retried request
            function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;

                return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
            },
        ]);

        // Create a handler stack with the mock handler
        $handlerStack = HandlerStack::create($mockHandler);

        // Create an instance of GuzzleFetchClient with the custom handler stack
        $fetchClient = new MockGuzzleHttpClient([
            'retries' => 1,
            'timeoutMillis' => 10000,
            'retryBackoffMinMillis' => 100,
            'retryBackoffScalar' => 2,
            'retryBackoffMaxMillis' => 500,
        ], $handlerStack);

        $client = new RemoteEvaluationClient($this->apiKey, RemoteEvaluationConfig::builder()->fetchClient($fetchClient)->build());

        // Expect a successful response after auto-retry
        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);

        // Assert the number of requests sent (including retries)
        $this->assertEquals(2, $requestCounter);
    }

    public function testretryOnceTimeoutFirstThenSucceedWithZeroBackoff()
    {
        // Initialize the request counter
        $requestCounter = 0;

        // Set up the mock handler
        $mockHandler = new MockHandler([
            // Simulate a failure (e.g., timeout) for the first request
            function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;

                return new RequestException('Error Communicating with Server', $request);
            },
            // Simulate a successful response for the retried request
            function (RequestInterface $request, array $options) use (&$requestCounter) {
                $requestCounter++;

                return new Response(200, [], '{"sdk-ci-test":{"key":"on","payload":"payload"}}');
            },
        ]);

        // Create a handler stack with the mock handler
        $handlerStack = HandlerStack::create($mockHandler);

        // Create an instance of GuzzleFetchClient with the custom handler stack
        $fetchClient = new MockGuzzleHttpClient([
            'retries' => 1,
            'timeoutMillis' => 10000,
            'retryBackoffMinMillis' => 0,
            'retryBackoffScalar' => 2,
            'retryBackoffMaxMillis' => 0,
        ], $handlerStack);

        $client = new RemoteEvaluationClient($this->apiKey, RemoteEvaluationConfig::builder()->fetchClient($fetchClient)->build());

        // Expect a successful response after auto-retry
        $variants = $client->fetch($this->testUser);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);

        // Assert the number of requests sent (including retries)
        $this->assertEquals(2, $requestCounter);
    }

    public function testFetchWithFlagKeysOptionsSuccess()
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser, ['sdk-ci-test']);
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals(1, sizeof($variants));
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testExperimentInitializeRemote()
    {
        $experiment = new Experiment();
        $client = $experiment->initializeRemote($this->apiKey);
        $this->assertEquals($client, $experiment->initializeRemote($this->apiKey));
    }
}
