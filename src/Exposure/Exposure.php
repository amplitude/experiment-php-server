<?php

namespace AmplitudeExperiment\Exposure;

use AmplitudeExperiment\Amplitude\Event;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use function AmplitudeExperiment\hashCode;
use const AmplitudeExperiment\Exposure\DAY_MILLIS;

/**
 * Exposure is a class that represents a user's exposure to a set of flags.
 */
class Exposure
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
        $this->timestamp = (int)floor(microtime(true) * 1000);
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

    /**
     * Convert an Exposure to Amplitude events
     *
     * @return array<Event>
     */
    public function toEvents(): array
    {
        $events = [];
        $canonicalized = $this->canonicalize();
        foreach ($this->variants as $flagKey => $variant) {
            $trackExposure = $variant->metadata['trackExposure'] ?? true;
            if ($trackExposure === false) {
                continue;
            }

            // Skip default variant exposures
            $isDefault = $variant->metadata['default'] ?? false;
            if ($isDefault) {
                continue;
            }

            $event = new Event('[Experiment] Exposure');
            $event->userId = $this->user->userId;
            $event->deviceId = $this->user->deviceId;
            $event->eventProperties = [];
            $event->userProperties = [];
            $event->time = $this->timestamp;

            $set = [];
            $unset = [];
            $flagType = $variant->metadata['flagType'] ?? null;

            if ($flagType != 'mutual-exclusion-group') {
                if ($variant->key) {
                    $set["[Experiment] {$flagKey}"] = $variant->key;
                } elseif ($variant->value) {
                    $set["[Experiment] {$flagKey}"] = $variant->value;
                }
            }
            $event->userProperties['$set'] = $set;
            $event->userProperties['$unset'] = $unset;

            $event->eventProperties['[Experiment] Flag Key'] = $flagKey;
            if ($variant->key) {
                $event->eventProperties['[Experiment] Variant'] = $variant->key;
            } elseif ($variant->value) {
                $event->eventProperties['[Experiment] Variant'] = $variant->value;
            }
            if ($variant->metadata) {
                $event->eventProperties['metadata'] = $variant->metadata;
            }

            $hash = hashCode("{$flagKey} {$canonicalized}");

            $event->insertId = "{$event->userId} {$event->deviceId} {$hash} " .
                floor($this->timestamp / DAY_MILLIS);

            if ($this->user->groups) {
                $event->groups = $this->user->groups;
            }

            $events[] = $event;
        }
        return $events;
    }
}

