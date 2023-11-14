<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\User;

class Assignment {
    public User $user;
    public array $results;
    public int $timestamp;

    public function __construct(User $user, array $results) {
        $this->user = $user;
        $this->results = $results;
        $this->timestamp = time();
    }

    public function canonicalize(): string {
        $canonical = trim("{$this->user->userId} {$this->user->deviceId}");

        foreach (array_keys($this->results) as $key) {
            $value = $this->results[$key];
            $canonical .= " " . trim($key) . " " . trim($value['value']);
        }

        return $canonical;
    }
}
