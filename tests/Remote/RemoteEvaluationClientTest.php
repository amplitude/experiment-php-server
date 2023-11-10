<?php

namespace AmplitudeExperiment\Test\Remote;

use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\FetchOptions;
use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;
use AmplitudeExperiment\User;
use Exception;
use PHPUnit\Framework\TestCase;

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

    /**
     * @throws Exception
     */
    public function testFetchSuccess()
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser)->wait();
        $variant = $variants['sdk-ci-test'];
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    /**
     * @throws Exception
     */
    public function testFetchWithNoRetriesTimeoutFailure()
    {
        $config = RemoteEvaluationConfig::builder()
            ->fetchRetries(0)
            ->fetchTimeoutMillis(1)
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);
        $variants = $client->fetch($this->testUser)->wait();
        $this->assertEquals([], $variants);
    }

    /**
     * @throws Exception
     */
    public function testFetchNoRetriesTimeoutFailureRetrySuccess()
    {
        $config = RemoteEvaluationConfig::builder()
            ->fetchRetries(1)
            ->fetchTimeoutMillis(1)
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);
        $variants = $client->fetch($this->testUser)->wait();
        $variant = $variants['sdk-ci-test'];
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    /**
     * @throws Exception
     */
    public function testFetchRetryOnceTimeoutFirstThenSucceedWithZeroBackoff()
    {
        $config = RemoteEvaluationConfig::builder()
            ->fetchRetries(1)
            ->fetchTimeoutMillis(1)
            ->fetchRetryBackoffMinMillis(0)
            ->fetchRetryTimeoutMillis(10000)
            ->build();
        $client = new RemoteEvaluationClient($this->apiKey, $config);
        $variants = $client->fetch($this->testUser)->wait();
        $variant = $variants['sdk-ci-test'];
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    /**
     * @throws Exception
     */
    public function testFetchWithFlagKeysOptionsSuccess()
    {
        $client = new RemoteEvaluationClient($this->apiKey);
        $variants = $client->fetch($this->testUser, new FetchOptions(['sdk-ci-test']))->wait();
        $variant = $variants['sdk-ci-test'];
        $this->assertEquals(1, sizeof($variants));
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    public function testExperimentInitializeRemote()
    {
        $experiment = new Experiment();
        $client = $experiment->initializeRemote($this->apiKey);
        $this->assertEquals($client, $experiment->initializeRemote($this->apiKey));
    }
}
