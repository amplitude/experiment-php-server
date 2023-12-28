<?php

namespace AmplitudeExperiment\Flag;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

class FlagConfigService
{
    private LoggerInterface $logger;
    public FlagConfigFetcher $fetcher;

    /**
     * @var array<string, mixed>
     */
    public array $cache;

    /**
     * @param array<string, mixed> $bootstrap
     */
    public function __construct(FlagConfigFetcher $fetcher, LoggerInterface $logger, array $bootstrap)
    {
        $this->fetcher = $fetcher;
        $this->logger = $logger;
        $this->cache = $bootstrap;
    }

    public function start(): void
    {
        $this->logger->debug('[Experiment] Flag service - start');

        // Fetch initial flag configs and await the result.
        $this->refresh();
    }

    private function refresh(): void
    {
        $this->logger->debug('[Experiment] Flag config update');
        try {
            $flagConfigs = $this->fetcher->fetch();
            $this->cache = $flagConfigs;
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
}
