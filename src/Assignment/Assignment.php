<?php

namespace AmplitudeExperiment\Assignment;

use AmplitudeExperiment\Amplitude\Event;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use RuntimeException;
use function AmplitudeExperiment\hashCode;

/**
 * Event class for tracking assignments to Amplitude Experiment.
 */
class Assignment
{
    public User $user;
    /**
     * @var array<string, Variant>
     */
    public array $variants;
    public int $timestamp;
    public string $apiKey;
    public int $minIdLength;

    /**
     * @param array<string, Variant> $variants
     */
    public function __construct(User $user, array $variants, string $apiKey = '', int $minIdLength = AssignmentConfig::DEFAULTS['minIdLength'])
    {
        $this->user = $user;
        $this->variants = $variants;
        $this->timestamp = (int)floor(microtime(true) * 1000);
        $this->apiKey = $apiKey;
        $this->minIdLength = $minIdLength;
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
     * Convert an Assignment to an Amplitude event
     */
    public function toEvent(): Event
    {
        $event = new Event('[Experiment] Assignment');
        $event->userId = $this->user->userId;
        $event->deviceId = $this->user->deviceId;
        $event->eventProperties = [];
        $event->userProperties = [];
        $event->time = $this->timestamp;

        $set = [];
        $unset = [];
        foreach ($this->variants as $flagKey => $variant) {
            if (!$variant->key) {
                continue;
            }
            $event->eventProperties["{$flagKey}.variant"] = $variant->key;
            $version = $variant->metadata['flagVersion'] ?? null;
            $segmentName = $variant->metadata['segmentName'] ?? null;
            if ($version && $segmentName) {
                $event->eventProperties["{$flagKey}.details"] = "v{$version} rule:{$segmentName}";
            }
            $flagType = $variant->metadata['flagType'] ?? null;
            $default = $variant->metadata['default'] ?? false;
            if ($flagType == FLAG_TYPE_MUTUAL_EXCLUSION_GROUP) {
                continue;
            } elseif ($default) {
                $unset["[Experiment] {$flagKey}"] = '-';
            } else {
                $set["[Experiment] {$flagKey}"] = $variant->key;
            }
        }

        $event->userProperties['$set'] = $set;
        $event->userProperties['$unset'] = $unset;

        $hash = hashCode($this->canonicalize());

        $event->insertId = "{$event->userId} {$event->deviceId} {$hash} " .
            floor($this->timestamp / DAY_MILLIS);

        return $event;
    }


    /**
     * Convert an Assignment to an array representation of an Amplitude event
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->toEvent()->toArray();
    }

    /**
     * Convert an Assignment to an Amplitude event JSON string
     * @throws RuntimeException
     */
    public function toJSONString(): string
    {
        $jsonString = json_encode($this->toArray());
        if (!$jsonString) {
            throw new RuntimeException('Failed to encode Assignment to JSON string');
        }
        return $jsonString;
    }

    /**
     * Convert an Assignment to a JSON string that can be used as a payload to the Amplitude event upload API
     * @throws RuntimeException
     */
    public function toJSONPayload(): string
    {
        $payload = ["api_key" => $this->apiKey, "events" => [$this->toEvent()], "options" => ["min_id_length" => $this->minIdLength]];
        $jsonString = json_encode($payload);
        if (!$jsonString) {
            throw new RuntimeException('Failed to encode Assignment to JSON payload');
        }
        return $jsonString;
    }
}
