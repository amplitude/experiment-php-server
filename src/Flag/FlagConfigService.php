<?php

namespace AmplitudeExperiment\Flag;

use Amp\CancellationToken;
use Amp\CancellationTokenSource;
use Amp\Deferred;
use AmplitudeExperiment\BackoffPolicy;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;
use Monolog\Logger;
use React\EventLoop\Loop;
use function AmplitudeExperiment\doWithBackoff;
use function AmplitudeExperiment\initializeLogger;

require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../Backoff.php';

class FlagConfigService
{
    private Logger $logger;
    private int $pollingIntervalMillis;
    private $poller = null;
    private ?CancellationTokenSource $cancellationToken = null;
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

            $this->cancellationToken = new CancellationTokenSource();

            // Create a coroutine for the poller
            $coroutine = function () {
                try {
                    $this->cancellationToken->getToken()->throwIfRequested();

                    $this->refresh()->then(function ($exception) {
                        if ($exception instanceof \Exception) {
                            $this->logger->debug('[Experiment] flag config refresh failed: ' . $exception->getMessage());
                        }
                    });

                } catch (\Amp\CancelledException $e) {
                    return;
                }
            };

            Loop::repeat(1000, function () use ($coroutine) {
                if ($this->cancellationToken->isCancelled()) {
                    return; // Exit the loop if cancellation is requested
                }

                // Enqueue a task to be executed
                \Amp\Loop::defer(function () use ($coroutine) {
                    $coroutine();
                });
            });



            $this->poller = true;

            // Fetch initial flag configs and await the result.
            doWithBackoff(
                function () {
                    return $this->refresh();
                },
                new BackoffPolicy(5, 1, 1, 1)
            )->wait();
        }
    }

    public function stop()
    {
        if ($this->poller && $this->cancellationToken) {
            $this->cancellationToken->cancel(); // Signal the coroutine to stop
            \Amp\Loop::stop(); // Stop the event loop
            $this->poller = null;
            $this->cancellationToken = null;
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
