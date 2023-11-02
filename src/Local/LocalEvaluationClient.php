<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\Flag\FlagConfigFetcher;
use AmplitudeExperiment\Flag\FlagConfigService;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use Monolog\Logger;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Utils.php';

class LocalEvaluationClient
{
    private string $apiKey;
    private LocalEvaluationConfig $config;
    private FlagConfigService $flagConfigService;
    private EvaluationEngine $evaluation;
    private Logger $logger;

    public function __construct(string $apiKey, ?LocalEvaluationConfig $config)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? LocalEvaluationConfig::builder()->build();
        $fetcher = new FlagConfigFetcher($apiKey, $this->config->serverUrl, $this->config->debug);
        $this->flagConfigService = new FlagConfigService($fetcher, $this->config->flagConfigPollingIntervalMillis, $this->config->debug);
        $this->logger = initializeLogger($this->config->debug ? Logger::DEBUG : Logger::INFO);
        $this->evaluation = new EvaluationEngine();
    }

    public function start()
    {
        $this->flagConfigService->start();
    }

    public function stop()
    {
        $this->flagConfigService->stop();
    }

    public function evaluate(User $user, array $flagKeys = []): array
    {
        $flags = $this->flagConfigService->getFlagConfigs();
        $this->logger->debug('[Experiment] evaluate - user: ' . json_encode($user) . 'flags: ' . json_encode($flags));
        $results = $this->evaluation->evaluate($this->toUserContext($user), $flags);
        $variants = [];
        $filter = !empty($flagKeys);

        foreach ($results as $flagKey => $flagResult) {
            $included = !$filter || in_array($flagKey, $flagKeys);
            if ($included) {
                $variants[$flagKey] = new Variant($flagResult['key'], $flagResult['payload'] ?? null);
            }
        }

        $this->logger->debug('[Experiment] evaluate - variants: ', $variants);
        return $variants;
    }

    private function toUserContext(User $user): array
    {
        return ["user" => $user->toArray()];
    }
}
