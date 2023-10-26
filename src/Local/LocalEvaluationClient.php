<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Flag\FlagConfigFetcher;
use AmplitudeExperiment\Flag\FlagConfigService;
use AmplitudeExperiment\User;

class LocalEvaluationClient
{
    private string $apiKey;
    private LocalEvaluationConfig $config;
    private FlagConfigService $flagConfigService;

    public function __construct(string $apiKey, ?LocalEvaluationConfig $config)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? LocalEvaluationConfig::builder()->build();
        $fetcher = new FlagConfigFetcher($apiKey, $this->config->serverUrl, $this->config->debug);
        $this->flagConfigService = new FlagConfigService($fetcher, $this->config->flagConfigPollingIntervalMillis, $this->config->debug);
    }

    public function start()
    {
        $this->flagConfigService->start();
    }

    public function stop(){
        $this->flagConfigService->stop();
    }

    // TODO type check on map-array of variants returned?
    public function evaluate(User $user, array $flagKeys): array
    {
        return [];
    }

}
