<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Assignment\Assignment;
use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Assignment\AssignmentFilter;
use AmplitudeExperiment\Assignment\AssignmentService;
use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\Flag\FlagConfigFetcher;
use AmplitudeExperiment\Flag\FlagConfigService;
use AmplitudeExperiment\Http\GuzzleHttpClient;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use Exception;
use Psr\Log\LoggerInterface;
use function AmplitudeExperiment\EvaluationCore\topologicalSort;

require_once __DIR__ . '/../EvaluationCore/Util.php';

/**
 * Experiment client for evaluating variants for a user locally.
 * @category Core Usage
 */
class LocalEvaluationClient
{
    private LocalEvaluationConfig $config;
    private FlagConfigService $flagConfigService;
    private EvaluationEngine $evaluation;
    private LoggerInterface $logger;
    private ?AssignmentService $assignmentService = null;

    public function __construct(string $apiKey, ?LocalEvaluationConfig $config = null)
    {
        $this->config = $config ?? LocalEvaluationConfig::builder()->build();
        $this->logger = new InternalLogger($this->config->logger ?? new DefaultLogger(), $this->config->logLevel);
        $httpClient = $config->httpClient ?? $this->config->httpClient ?? new GuzzleHttpClient($this->config->guzzleClientConfig);
        $fetcher = new FlagConfigFetcher($apiKey, $this->logger, $httpClient, $this->config->serverUrl);
        $this->flagConfigService = new FlagConfigService($fetcher, $this->logger, $this->config->bootstrap);
        $this->initializeAssignmentService($this->config->assignmentConfig);
        $this->evaluation = new EvaluationEngine();
    }

    /**
     * Fetch latest flag configurations.
     */
    public function refreshFlagConfigs(): void
    {
        $this->flagConfigService->refresh();
    }

    /**
     * Locally evaluates flag variants for a user.
     *
     * This function will only evaluate flags for the keys specified in the
     * flagKeys argument. If flagKeys is missing or empty, all flags in the
     * {@link FlagConfigService} will be evaluated.
     *
     * @param User $user The user to evaluate
     * @param array<string> $flagKeys The flags to evaluate with the user. If empty, all flags
     * from the flag cache are evaluated.
     * @return array<Variant> evaluated variants
     */
    public function evaluate(User $user, array $flagKeys = []): array
    {
        $flags = $this->flagConfigService->getFlagConfigs();
        try {
            $flags = topologicalSort($flags, $flagKeys);
        } catch (Exception $e) {
            $this->logger->error('[Experiment] Evaluate - error sorting flags: ' . $e->getMessage());
        }
        $this->logger->debug('[Experiment] Evaluate - user: ' . json_encode($user->toArray()) . ' with flags: ' . json_encode($flags));
        $results = array_map('AmplitudeExperiment\Variant::convertEvaluationVariantToVariant', $this->evaluation->evaluate($user->toEvaluationContext(), $flags));
        $this->logger->debug('[Experiment] Evaluate - variants:' . json_encode($results));
        if ($this->assignmentService) {
            $this->assignmentService->track(new Assignment($user, $results));
        }
        return $results;
    }

    public function stop(): void {
        if ($this->assignmentService) {
            $this->assignmentService->amplitude->stop();
        }
    }


    /**
     * @return array<string, mixed> flag configurations.
     */
    public function getFlagConfigs(): array
    {
        return $this->flagConfigService->getFlagConfigs();
    }

    private function initializeAssignmentService(?AssignmentConfig $config): void
    {
        if ($config) {
            $this->assignmentService = new AssignmentService(
                new Amplitude($config->apiKey,
                    $this->logger,
                    $config->amplitudeConfig),
                new AssignmentFilter($config->cacheCapacity));
        }
    }
}
