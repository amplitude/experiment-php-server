<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;

class Assignment
{
    public User $user;
    /**
     * @var array<string, Variant>
     */
    public array $variants;
    public int $timestamp;

    /**
     * @param array<string, Variant> $variants
     */
    public function __construct(User $user, array $variants)
    {
        $this->user = $user;
        $this->variants = $variants;
        $this->timestamp = (int) floor(microtime(true) * 1000);
    }

    public function canonicalize(): string
    {
        $canonical = trim("{$this->user->userId} {$this->user->deviceId}") . ' ';
        $sortedKeys = array_keys($this->variants);
        sort($sortedKeys);
        foreach ($sortedKeys as $key) {
            $variant = $this->variants[$key];
            if (!$variant->key) {
                continue;
            }
            $canonical .= trim($key) . ' ' . trim($variant->key) . ' ';
        }
        return $canonical;
    }
}
