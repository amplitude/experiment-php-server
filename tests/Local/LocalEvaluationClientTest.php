<?php

namespace AmplitudeExperiment\Test\Local;

use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;

class LocalEvaluationClientTest extends TestCase
{
    private string $apiKey = 'server-qz35UwzJ5akieoAdIgzM4m9MIiOLXLoz';
    private User $testUser;

    public function __construct()
    {
        parent::__construct();
        $this->testUser = User::builder()
            ->userId('test_user')
            ->deviceId('test_device')
            ->build();
    }
    public function test() {
        $experiment = new Experiment();
        $config = LocalEvaluationConfig::builder()->debug(true)->build();
        $client = $experiment->initializeLocal($this->apiKey, $config);
        $client->start();
        $client->evaluate($this->testUser);
        $client->stop();
    }

    public function testEvaluateAllFlags()
    {
        $experiment = new Experiment();
        $config = LocalEvaluationConfig::builder()->debug(true)->build();
        $client = $experiment->initializeLocal($this->apiKey, $config);
        $client->start();
        $variants = $client->evaluate($this->testUser);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        self::assertEquals(new Variant("on", "payload"), $variant);
    }
}
