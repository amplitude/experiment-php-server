<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\BackoffPolicy;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use function AmplitudeExperiment\doWithBackoff;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../Backoff.php';

class FlagConfigService
{
    private Logger $logger;
    private int $pollingIntervalMillis;
    private ?TimerInterface $poller = null;

    public FlagConfigFetcher $fetcher;
    public array $cache;

    public function __construct(FlagConfigFetcher $fetcher, int $pollingIntervalMillis = LocalEvaluationConfig::DEFAULTS["flagConfigPollingIntervalMillis"], bool $debug = false)
    {
        $this->fetcher = $fetcher;
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
                        $this->refresh()->wait();
                    } catch (Exception $e) {
                        $this->logger->debug('[Experiment] flag config refresh failed: ' . $e->getMessage());
                    }
                }
            );

            // Fetch initial flag configs and await the result.
            doWithBackoff(
                function () {
                    return $this->refresh();
                }
                , new BackoffPolicy(5, 1, 1, 1))->wait();
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

    public function refresh(): PromiseInterface
    {
        $this->logger->debug('[Experiment] flag config update');
        return Create::promiseFor($this->cache = $this->fetcher->fetch()->wait());
    }

    public function getFlagConfigs(): array
    {
        return $this->cache;
    }

}
