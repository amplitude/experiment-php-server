<?php

namespace AmplitudeExperiment\Test\Local;

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
        $config = LocalEvaluationConfig::builder()->debug(true)->build();
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
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    public function testEvaluateOneFlagSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ["sdk-local-evaluation-ci-test"]);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        self::assertEquals("on", $variant->key);
        self::assertEquals("payload", $variant->payload);
    }

    public function testEvaluateWithDependenciesWithFlagKeysSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ['sdk-ci-local-dependencies-test']);
        $variant = $variants['sdk-ci-local-dependencies-test'];
        self::assertEquals("control", $variant->key);
        self::assertEquals(null, $variant->payload);
    }

    public function testEvaluateWithDependenciesWithUnknownFlagKeysNoVariant()
    {
        $variants = $this->client->evaluate($this->testUser, ['does-not-exist']);
        self::assertFalse(isset($variants['sdk-ci-local-dependencies-test']));
    }

    public function testEvaluateWithDependenciesVariantHeldOut()
    {
        $variants = $this->client->evaluate($this->testUser);
        $variant = $variants['sdk-ci-local-dependencies-test-holdout'];
        self::assertEquals("off", $variant->key);
        self::assertEquals(null, $variant->payload);
        self::assertTrue($variant->metadata["default"]);
    }
}
