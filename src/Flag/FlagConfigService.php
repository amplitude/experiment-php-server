<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\Backoff;
use AmplitudeExperiment\Util;
use GuzzleHttp\Promise\PromiseInterface;
use Monolog\Logger;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Util.php';

class FlagConfigService
{
    private Logger $logger;
    public FlagConfigFetcher $fetcher;
    public array $cache;

    public function __construct(FlagConfigFetcher $fetcher, bool $debug, array $bootstrap)
    {
        $this->fetcher = $fetcher;
        $this->logger = initializeLogger($debug);
        $this->cache = $bootstrap;
    }

    public function start(): PromiseInterface
    {
        $this->logger->debug('[Experiment] Flag service - start');

        // Fetch initial flag configs and await the result.
        return Backoff::doWithBackoff(
            function () {
                return $this->refresh();
            },
            new Backoff(5, 1, 1, 1)
        );
    }

    private function refresh(): PromiseInterface
    {
        $this->logger->debug('[Experiment] Flag config update');
        return $this->fetcher->fetch()->then(
            function (array $flagConfigs) {
                $this->cache = $flagConfigs;
                $this->logger->debug('[Experiment] Flag config update success');
            },
            function (string $error) {
                $this->logger->debug('[Experiment] Flag config update failed:' . $error);
            }
        );
    }

    public function getFlagConfigs(): array
    {
        return $this->cache;
    }
}
