<?php

namespace AmplitudeExperiment\Assignment;

class ListNode
{
    public ?ListNode $prev = null;
    public ?ListNode $next = null;
    /**
     * @var mixed
     */
    public $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->prev = null;
        $this->next = null;
        $this->data = $data;
    }
}

class CacheItem
{
    public string $key;
    /**
     * @var mixed
     */
    public $value;
    public int $createdAt;

    /**
     * @param mixed $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
        $this->createdAt = (int) floor(microtime(true) * 1000);
    }
}

class LRUCache
{
    private int $capacity;
    private int $ttlMillis;
    /**
     * @var array<string, ListNode>
     */
    private array $cache;
    private ?ListNode $head = null;
    private ?ListNode $tail = null;

    public function __construct(int $capacity, int $ttlMillis)
    {
        $this->capacity = $capacity;
        $this->ttlMillis = $ttlMillis;
        $this->cache = [];
        $this->head = null;
        $this->tail = null;
    }

    /**
     * @param mixed $value
     */
    public function put(string $key, $value): void
    {
        if (isset($this->cache[$key])) {
            $this->removeFromList($key);
        } elseif (count($this->cache) >= $this->capacity) {
            $this->evictLRU();
        }

        $cacheItem = new CacheItem($key, $value);
        $node = new ListNode($cacheItem);
        $this->cache[$key] = $node;
        $this->insertToList($node);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        if (isset($this->cache[$key])) {
            $node = $this->cache[$key];
            $timeElapsed = floor(microtime(true) * 1000) - $node->data->createdAt;

            if ($timeElapsed > $this->ttlMillis) {
                $this->remove($key);
                return null;
            }

            $this->removeFromList($key);
            $this->insertToList($node);
            return $node->data->value;
        }

        return null;
    }

    public function remove(string $key): void
    {
        $this->removeFromList($key);
        unset($this->cache[$key]);
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->head = null;
        $this->tail = null;
    }

    private function evictLRU(): void
    {
        if ($this->head) {
            $this->remove($this->head->data->key);
        }
    }

    private function removeFromList(string $key): void
    {
        $node = $this->cache[$key];

        if ($node->prev) {
            $node->prev->next = $node->next;
        } else {
            $this->head = $node->next;
        }

        if ($node->next) {
            $node->next->prev = $node->prev;
        } else {
            $this->tail = $node->prev;
        }
    }

    private function insertToList(ListNode $node): void
    {
        if ($this->tail) {
            $this->tail->next = $node;
            $node->prev = $this->tail;
            $node->next = null;
            $this->tail = $node;
        } else {
            $this->head = $node;
            $this->tail = $node;
        }
    }
}
