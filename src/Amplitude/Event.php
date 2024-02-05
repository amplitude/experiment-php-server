<?php

namespace AmplitudeExperiment\Amplitude;

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
            'insert_id' => $this->insertId,]);
    }
}
