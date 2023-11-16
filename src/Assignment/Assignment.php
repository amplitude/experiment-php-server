<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\User;

class Assignment
{
    public User $user;
    public array $results;
    public int $timestamp;

    public function __construct(User $user, array $results)
    {
        $this->user = $user;
        $this->results = $results;
        $this->timestamp = floor(microtime(true) * 1000);
    }

    public function canonicalize(): string
    {
        $canonical = trim("{$this->user->userId} {$this->user->deviceId}");
        $sortedKeys = array_keys($this->results);
        sort($sortedKeys);
        foreach ($sortedKeys as $key) {
            $value = $this->results[$key];
            $canonical .= " " . trim($key) . " " . trim($value['key']);
        }
        return $canonical;
    }
}
