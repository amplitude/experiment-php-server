<?php

namespace AmplitudeExperiment\Amplitude;

use RuntimeException;

class Event
{
    public ?string $eventType = null;
    /**
     * @var ?array<mixed>
     */
    public ?array $eventProperties = null;
    /**
     * @var ?array<mixed>
     */
    public ?array $userProperties = null;
    public ?string $userId = null;
    public ?string $deviceId = null;
    public ?string $insertId = null;
    public ?int $time = null;

    public function __construct(string $eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'event_type' => $this->eventType,
            'event_properties' => $this->eventProperties,
            'user_properties' => $this->userProperties,
            'user_id' => $this->userId,
            'device_id' => $this->deviceId,
            'insert_id' => $this->insertId,
            'time' => $this->time]);
    }

    /**
     * @throws RuntimeException
     */
    public function toJSONString(): string
    {
        $jsonString = json_encode($this->toArray());
        if (!$jsonString) {
            throw new RuntimeException('Failed to encode Event to JSON string');
        }
        return $jsonString;
    }
}
