<?php

namespace AmplitudeExperiment\Flag;

use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

class InMemoryFlagConfigCache implements FlagConfigCache
{
    private array $cache;

    public function __construct(array $flagConfigs = [])
    {
        $this->cache = $flagConfigs;
    }

    public function get(string $flagKey): PromiseInterface
    {
        return Create::promiseFor($this->cache[$flagKey] ?? null);
    }

    public function getAll(): PromiseInterface
    {
        return Create::promiseFor($this->cache);
    }

    public function put(string $flagKey, array $flagConfig): PromiseInterface
    {
        return Create::promiseFor($this->cache[$flagKey] = $flagConfig);
    }

    public function putAll(array $flagConfigs): void
    {
        foreach ($flagConfigs as $key => $flag) {
            if ($flag) {
                $this->cache[$key] = $flag;
            }
        }
    }

    public function delete(string $flagKey): void
    {
        unset($this->cache[$flagKey]);
    }

    public function clear(): void
    {
        $this->cache = [];
    }
}
