<?php

namespace AmplitudeExperiment\Test\Util;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * Minimal PSR-18 client for tests. Each call consumes the next entry from
 * the queue. Queue entries may be:
 *  - {@link ResponseInterface}: returned directly
 *  - {@link Throwable}: thrown
 *  - callable(RequestInterface): receives the request; may assert on it,
 *    then return a ResponseInterface (returned) or a Throwable (thrown)
 */
class MockPsr18Client implements ClientInterface
{
    /** @var array<int, callable|ResponseInterface|Throwable> */
    private array $queue;
    public int $callCount = 0;

    /**
     * @param array<int, callable|ResponseInterface|Throwable> $queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->callCount++;
        if (empty($this->queue)) {
            throw new RuntimeException('MockPsr18Client queue exhausted');
        }
        $next = array_shift($this->queue);
        if (is_callable($next)) {
            $next = $next($request);
        }
        if ($next instanceof Throwable) {
            throw $next;
        }
        return $next;
    }
}
