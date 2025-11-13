<?php

namespace AmplitudeExperiment\Local;

use AmplitudeExperiment\Assignment\AssignmentConfig;
use AmplitudeExperiment\Assignment\AssignmentService;
use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use AmplitudeExperiment\Exposure\DefaultExposureFilter;
use AmplitudeExperiment\Exposure\DefaultExposureTrackingProvider;
use AmplitudeExperiment\Exposure\Exposure;
use AmplitudeExperiment\Exposure\ExposureConfig;
use AmplitudeExperiment\Exposure\ExposureService;
use AmplitudeExperiment\Flag\FlagConfigFetcher;
use AmplitudeExperiment\Flag\FlagConfigService;
use AmplitudeExperiment\Http\GuzzleHttpClient;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use function AmplitudeExperiment\EvaluationCore\topologicalSort;

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
    private ExposureService $exposureService;

    public function __construct(string $apiKey, ?LocalEvaluationConfig $config = null)
    {
        $this->config = $config ?? LocalEvaluationConfig::builder()->build();
        $this->logger = new InternalLogger($this->config->logger ?? new DefaultLogger(), $this->config->logLevel);
        $httpClient = $config->httpClient ?? $this->config->httpClient ?? new GuzzleHttpClient($this->config->guzzleClientConfig);
        $fetcher = new FlagConfigFetcher($apiKey, $this->logger, $httpClient, $this->config->serverUrl);
        $this->flagConfigService = new FlagConfigService($fetcher, $this->logger, $this->config->bootstrap);
        $this->initializeAssignmentService($this->config->assignmentConfig);
        $this->initializeExposureService($apiKey, $this->config->exposureConfig);
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
     * @param EvaluateOptions $options The options for the evaluation.
     * @return array<string, Variant> evaluated variants
     */
    public function evaluate(User $user, array $flagKeys = [], EvaluateOptions $options = null): array
    {
        $options = $options ?? new EvaluateOptions();

        // Get translated flags from the flag config service
        $flags = $this->flagConfigService->getTranslatedFlags();

        try {
            // Sort flags topologically based on dependencies
            $flags = topologicalSort($flags, $flagKeys);
        } catch (\Exception $e) {
            $this->logger->error('[Experiment] Evaluate - error sorting flags: ' . $e->getMessage());
        }

        $this->logger->debug('[Experiment] Evaluate - user: ' . json_encode($user->toArray()) . ' with flags: ' . json_encode(array_map(function($flag) { return $flag->key; }, $flags)));

        // Evaluate the user against the flags
        $evaluationResults = $this->evaluation->evaluate($user->toEvaluationContext(), $flags);

        // Convert evaluation results to Variant objects
        $results = [];
        foreach ($evaluationResults as $key => $evaluationVariant) {
            $results[$key] = Variant::convertEvaluationVariantToVariant($evaluationVariant);
        }

        $this->logger->debug('[Experiment] Evaluate - variants:' . json_encode($results));

        // Track assignments if assignment service is configured
        if ($this->assignmentService) {
            $this->assignmentService->track($this->assignmentService->createAssignment($user, $results));
        }
        if ($options->tracksExposure) {
            $this->exposureService->track(new Exposure($user, $results));
        }

        return $results;
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
                $config->assignmentTrackingProvider,
                $config->assignmentFilter,
                $config->apiKey,
                $config->minIdLength);
        }
    }

    private function initializeExposureService(string $deploymentKey, ?ExposureConfig $config): void
    {
        if ($config == null) {
            $config = ExposureConfig::builder($deploymentKey, new DefaultExposureTrackingProvider(new Amplitude($deploymentKey)))->build();
        }
        $exposureTrackingProvider = $config->exposureTrackingProvider ?? new DefaultExposureTrackingProvider($config->apiKey ?? $deploymentKey);
        $exposureFilter = $config->exposureFilter ?? new DefaultExposureFilter();
        $this->exposureService = new ExposureService($exposureTrackingProvider, $exposureFilter);
    }
}
