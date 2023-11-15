<?php

namespace AmplitudeExperiment\Assignment;

class ListNode {
    public $prev;
    public $next;
    public $data;

    public function __construct($data) {
        $this->prev = null;
        $this->next = null;
        $this->data = $data;
    }
}

class CacheItem {
    public $key;
    public $value;
    public $createdAt;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
        $this->createdAt = floor(microtime(true) * 1000);
    }
}

class LRUCache {
    private $capacity;
    private $ttlMillis;
    private $cache;
    private $head;
    private $tail;

    public function __construct($capacity, $ttlMillis) {
        $this->capacity = $capacity;
        $this->ttlMillis = $ttlMillis;
        $this->cache = [];
        $this->head = null;
        $this->tail = null;
    }

    public function put($key, $value): void {
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

    public function get($key) {
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

    public function remove($key): void {
        $this->removeFromList($key);
        unset($this->cache[$key]);
    }

    public function clear(): void {
        $this->cache = [];
        $this->head = null;
        $this->tail = null;
    }

    private function evictLRU(): void {
        if ($this->head) {
            $this->remove($this->head->data->key);
        }
    }

    private function removeFromList($key): void {
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

    private function insertToList($node): void {
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

