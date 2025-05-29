<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/Util.php';

class FlagConfigService
{
    private LoggerInterface $logger;
    public FlagConfigFetcher $fetcher;

    /**
     * @var array<string, mixed>
     */
    public array $cache;

    /**
     * @var EvaluationFlag[]
     */
    private array $translatedFlags = [];

    /**
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(FlagConfigFetcher $fetcher, LoggerInterface $logger, array $bootstrap)
    {
        $this->fetcher = $fetcher;
        $this->logger = $logger;
        $this->cache = $bootstrap;
        $this->translateFlags();
    }

    public function refresh(): void
    {
        $this->logger->debug('[Experiment] Flag config update');
        try {
            $flagConfigs = $this->fetcher->fetch();
            $this->cache = $flagConfigs;
            $this->translateFlags();
        } catch (ClientExceptionInterface $error) {
            $this->logger->error('[Experiment] Failed to fetch flag configs: ' . $error->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getFlagConfigs(): array
    {
        return $this->cache;
    }

    /**
     * @return EvaluationFlag[]
     */
    public function getTranslatedFlags(): array
    {
        return $this->translatedFlags;
    }

    /**
     * Translates raw flag configs into typed EvaluationFlag objects
     */
    private function translateFlags(): void
    {
        try {
            $this->translatedFlags = createFlagsFromArray($this->cache);
        } catch (\Exception $e) {
            $this->logger->error('[Experiment] Failed to translate flag configs: ' . $e->getMessage());
            $this->translatedFlags = [];
        }
    }
}
