<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\Flag\FlagConfigFetcher;
use AmplitudeExperiment\Flag\FlagConfigService;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Util;
use AmplitudeExperiment\Variant;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Logger;
use function AmplitudeExperiment\EvaluationCore\topologicalSort;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../EvaluationCore/Util.php';
require_once __DIR__ . '/../Util.php';

/**
 * Experiment client for evaluating variants for a user locally.
 * @category Core Usage
 */
class LocalEvaluationClient
{
    private string $apiKey;
    private LocalEvaluationConfig $config;
    private FlagConfigService $flagConfigService;
    private EvaluationEngine $evaluation;
    private Logger $logger;

    public function __construct(string $apiKey, ?LocalEvaluationConfig $config = null)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? LocalEvaluationConfig::builder()->build();
        $fetcher = new FlagConfigFetcher($apiKey, $this->config->serverUrl, $this->config->debug);
        $this->flagConfigService = new FlagConfigService($fetcher, $this->config->debug, $this->config->bootstrap);
        $this->logger = initializeLogger($this->config->debug ? Logger::DEBUG : Logger::INFO);
        $this->evaluation = new EvaluationEngine();
    }

    /**
     * Fetch initial flag configurations.
     *
     * The promise returned by this function is resolved when the initial call
     * to fetch the flag configuration completes.
     *
     */
    public function start(): PromiseInterface
    {
        return $this->flagConfigService->start();
    }

    /**
     * Locally evaluates flag variants for a user.
     *
     * This function will only evaluate flags for the keys specified in the
     * flagKeys argument. If flagKeys is missing or empty, all flags in the
     * {@link FlagConfigService} will be evaluated.
     *
     * @param $user User The user to evaluate
     * @param $flagKeys array The flags to evaluate with the user. If empty, all flags
     * from the flag cache are evaluated.
     * @returns array evaluated variants
     */
    public function evaluate(User $user, array $flagKeys = []): array
    {
        $flags = $this->flagConfigService->getFlagConfigs();
        try {
            $flags = topologicalSort($flags, $flagKeys);
        } catch (\Exception $e) {
            $this->logger->error('[Experiment] Evaluate - error sorting flags: ' . $e->getMessage());
        }
        $this->logger->debug('[Experiment] Evaluate - user: ' . json_encode($user->toArray()) . ' with flags: ' . json_encode($flags));
        $results = $this->evaluation->evaluate($user->toEvaluationContext(), $flags);
        $variants = [];
        $filter = !empty($flagKeys);

        foreach ($results as $flagKey => $flagResult) {
            $included = !$filter || in_array($flagKey, $flagKeys);
            if ($included) {
                $variants[$flagKey] = Variant::convertEvaluationVariantToVariant($flagResult);
            }
        }

        $this->logger->debug('[Experiment] Evaluate - variants:', $variants);
        return $variants;
    }
}
