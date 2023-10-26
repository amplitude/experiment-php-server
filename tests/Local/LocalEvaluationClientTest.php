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

    public function __construct()
    {
        parent::__construct();
        $this->testUser = User::builder()
            ->userId('test_user')
            ->build();
    }
    public function test() {
        $experiment = new Experiment();
        $config = LocalEvaluationConfig::builder()->debug(true)->build();
        $client = $experiment->initializeLocal($this->apiKey, $config);
        $client->start();
    }
}
