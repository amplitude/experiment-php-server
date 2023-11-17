<?php

namespace AmplitudeExperiment\Test\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\User;
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
        $config = LocalEvaluationConfig::builder()->debug(false)->build();
        $this->client = $experiment->initializeLocal($this->apiKey, $config);
    }

    public function setUp(): void
    {
        $this->client->start()->wait();
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

    public function testAssignment()
    {
        $aConfig = new AssignmentConfig('a6dd847b9d2f03c816d4f3f8458cdc1d');
        $config = LocalEvaluationConfig::builder()->debug(true)->assignmentConfig($aConfig)->build();
        $client = new LocalEvaluationClient($this->apiKey, $config);
        $client->start()->wait();
        $user = User::builder()->userId('tim.yiu@amplitude.com')->build();
        $client->evaluate($user);
        $this->assertTrue(true);
    }
}
