<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\BackoffPolicy;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use Exception;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use function AmplitudeExperiment\doWithBackoff;
use function AmplitudeExperiment\initializeLogger;

class FlagConfigService
{
    private Logger $logger;
    private int $pollingIntervalMillis;
    private ?TimerInterface $poller;

    public FlagConfigFetcher $fetcher;
    public array $cache;

    public function __construct($fetcher, $cache, $pollingIntervalMillis = LocalEvaluationConfig::DEFAULTS["flagConfigPollingIntervalMillis"], $debug = false)
    {
        $this->fetcher = $fetcher;
        $this->cache = $cache;
        $this->pollingIntervalMillis = $pollingIntervalMillis;
        $this->logger = initializeLogger($debug);
    }

    public function start()
    {
        if (!$this->poller) {
            $this->logger->debug('[Experiment] poller - start');

            // Use a timer to repeatedly trigger the callback function
            $this->poller = Loop::get()->addPeriodicTimer(
                $this->pollingIntervalMillis / 1000, // Convert to seconds
                function () {
                    try {
                        $this->refresh();
                    } catch (Exception $e) {
                        $this->logger->debug('[Experiment] flag config refresh failed: ' . $e->getMessage());
                    }
                }
            );

            // Fetch initial flag configs and await the result.
            doWithBackoff(
                function () {
                    $this->refresh();
                }
                , new BackoffPolicy(5, 1, 1, 1));
        }
    }

    public function stop()
    {
        if ($this->poller) {
            $this->logger->debug('[Experiment] poller - stop');
            Loop::get()->cancelTimer($this->poller);
            $this->poller = null;
        }
    }

    public function refresh()
    {
        $this->logger->debug('[Experiment] flag config update');
        $flagConfigs = $this->fetcher->fetch()->wait();
        $this->cache = $flagConfigs;
    }

    public function getFlagConfigs(): array
    {
        return $this->cache;
    }

}
