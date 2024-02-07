<?php

namespace AmplitudeExperiment\Test\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Logger\LogLevel;
use AmplitudeExperiment\User;
use Exception;
use PHPUnit\Framework\TestCase;

class LocalEvaluationClientTest extends TestCase
{
    private string $apiKey = 'server-qz35UwzJ5akieoAdIgzM4m9MIiOLXLoz';
    private User $testUser;
    private LocalEvaluationClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->testUser = User::builder()
            ->userId('test_user')
            ->deviceId('test_device')
            ->build();
        $experiment = new Experiment();
        $config = LocalEvaluationConfig::builder()->logLevel(LogLevel::DEBUG)->build();
        $this->client = $experiment->initializeLocal($this->apiKey, $config);
    }

    public function setUp(): void
    {
        $this->client->refreshFlagConfigs();
    }

    public function testEvaluateAllFlags()
    {
        $variants = $this->client->evaluate($this->testUser);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testEvaluateOneFlagSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ["sdk-local-evaluation-ci-test"]);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testEvaluateWithDependenciesWithFlagKeysSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ['sdk-ci-local-dependencies-test']);
        $variant = $variants['sdk-ci-local-dependencies-test'];
        $this->assertEquals("control", $variant->key);
        $this->assertEquals(null, $variant->payload);
    }

    public function testEvaluateWithDependenciesWithUnknownFlagKeysNoVariant()
    {
        $variants = $this->client->evaluate($this->testUser, ['does-not-exist']);
        $this->assertFalse(isset($variants['sdk-ci-local-dependencies-test']));
    }

    public function testEvaluateWithDependenciesVariantHeldOut()
    {
        $variants = $this->client->evaluate($this->testUser);
        $variant = $variants['sdk-ci-local-dependencies-test-holdout'];
        $this->assertEquals("off", $variant->key);
        $this->assertEquals(null, $variant->payload);
        $this->assertTrue($variant->metadata["default"]);
    }

    public function testGetFlagConfigs()
    {
        $flagConfigs = $this->client->getFlagConfigs();
        $bootstrapClient = new LocalEvaluationClient('', LocalEvaluationConfig::builder()->bootstrap($flagConfigs)->build());
        $variants = $bootstrapClient->evaluate($this->testUser);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    /**
     * @throws Exception
     */
    public function testAssignmentPerformanceAsync()
    {
        $flagConfigs = $this->client->getFlagConfigs();
        $assignmentConfig = AssignmentConfig::builder('a6dd847b9d2f03c816d4f3f8458cdc1d')->useBatch(false)->build();
        for ($i = 0; $i < 100; $i++) {
            $testClient = new LocalEvaluationClient('', LocalEvaluationConfig::builder()->assignmentConfig($assignmentConfig)->bootstrap($flagConfigs)->build());
            $user = User::builder()
                ->userId('tim.yiu@amplitude.com')
                ->deviceId('test_device_e' . $i)
                ->build();
            $variants = $testClient->evaluate($user, [], true);
            $variant = $variants['sdk-local-evaluation-ci-test'];
            $this->assertEquals("on", $variant->key);
            $this->assertEquals("payload", $variant->payload);
        }
    }

    /**
     * @throws Exception
     */
    public function testAssignmentPerformanceSync()
    {
        $flagConfigs = $this->client->getFlagConfigs();
        $assignmentConfig = AssignmentConfig::builder('a6dd847b9d2f03c816d4f3f8458cdc1d')->useBatch(true)->build();
        for ($i = 0; $i < 30; $i++) {
            $testClient = new LocalEvaluationClient('', LocalEvaluationConfig::builder()->assignmentConfig($assignmentConfig)->bootstrap($flagConfigs)->build());
            $user = User::builder()
                ->userId('tim.yiu@amplitude.com')
                ->deviceId('test_device' . $i)
                ->build();
            $variants = $testClient->evaluate($user);
            $variant = $variants['sdk-local-evaluation-ci-test'];
            $this->assertEquals("on", $variant->key);
            $this->assertEquals("payload", $variant->payload);
            usleep(100 * 1000);
            $testClient->stop();
        }
    }
}
