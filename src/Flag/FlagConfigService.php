<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\BackoffPolicy;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use function AmplitudeExperiment\doWithBackoff;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../Backoff.php';

class FlagConfigService
{
    private Logger $logger;
    private int $pollingIntervalMillis;
    public FlagConfigFetcher $fetcher;
    public array $cache;
    private ?LoopInterface $loop = null;
    private ?TimerInterface $timer = null;

    public function __construct(FlagConfigFetcher $fetcher, int $pollingIntervalMillis = LocalEvaluationConfig::DEFAULTS["flagConfigPollingIntervalMillis"], bool $debug = false)
    {
        $this->fetcher = $fetcher;
        $this->pollingIntervalMillis = $pollingIntervalMillis;
        $this->logger = initializeLogger($debug);
    }

    public function start()
    {
        $this->logger->debug('[Experiment] poller - start');

        if (!$this->loop) {
            $this->loop = Loop::get();

            // Schedule the initial run of the task
            $this->scheduleTask();

            // Fetch initial flag configs and await the result.
            doWithBackoff(
                function () {
                    return $this->refresh();
                },
                new BackoffPolicy(5, 1, 1, 1)
            )->then(function () {
                $this->loop->run(); // Start the event loop after the initial fetch.
            });
        }
    }

    private function refresh(): PromiseInterface
    {
        $this->logger->debug('[Experiment] flag config update');
        return Create::promiseFor($this->cache = $this->fetcher->fetch()->wait());
    }

    public function stop()
    {
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
            $this->loop = null;
        }
    }

    private function scheduleTask()
    {
        $this->timer = $this->loop->addPeriodicTimer(1, function () {
            $this->refresh()->then(function ($exception) {
                if ($exception instanceof \Exception) {
                    $this->logger->debug('[Experiment] flag config refresh failed: ' . $exception->getMessage());
                }
            });
        });
    }

    public function getFlagConfigs(): array
    {
        return $this->cache;
    }

}
