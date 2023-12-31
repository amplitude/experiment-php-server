<?php

namespace AmplitudeExperiment\Amplitude;

class Event
{
    public ?string $eventType = null;
    public ?array $eventProperties = null;
    public ?array $userProperties = null;
    public ?string $userId = null;
    public ?string $deviceId = null;
    public ?string $insertId = null;

    public function __construct(string $eventType)
    {
        $this->eventType = $eventType;
    }

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
