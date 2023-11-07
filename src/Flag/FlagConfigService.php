<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\Backoff;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Util;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use function Amp\asyncCall;
use function Amp\delay;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Util.php';

class FlagConfigService
{
    private Logger $logger;
    private int $pollingIntervalMillis;
    public FlagConfigFetcher $fetcher;
    public array $cache;
    private ?int $lastRequested = null;
    private LoopInterface $loop;

    public function __construct(FlagConfigFetcher $fetcher, int $pollingIntervalMillis = LocalEvaluationConfig::DEFAULTS["flagConfigPollingIntervalMillis"], bool $debug = false, array $bootstrap = [])
    {
        $this->fetcher = $fetcher;
        $this->pollingIntervalMillis = $pollingIntervalMillis;
        $this->logger = initializeLogger($debug);
        $this->loop = Loop::get();
        $this->cache = $bootstrap;
    }

    public function start(): PromiseInterface
    {
        $this->logger->debug('[Experiment] poller - start');

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
        $this->logger->debug('[Experiment] flag config update');
        $this->lastRequested = time();
        return Create::promiseFor($this->cache = $this->fetcher->fetch()->wait());
    }

    private function queueRefresh() {
        $this->loop->addTimer(1, function() {
            yield $this->refresh();
            $this->queueRefresh();
        });
    }

    public function getFlagConfigs(): array
    {
        if ($this->lastRequested == null || (float)time() - $this->lastRequested > $this->pollingIntervalMillis / 1000) {
            $this->refresh()->wait();
        }
        return $this->cache;
    }
}
