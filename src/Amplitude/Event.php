<?php

namespace AmplitudeExperiment\Amplitude;

use RuntimeException;

/**
 * @phpstan-type Payload array{
 *     event_type: string,
 *     event_properties: array<mixed>|null,
 *     user_properties: array<mixed>|null,
 *     user_id: string|null,
 *     device_id: string|null,
 *     insert_id: string|null,
 *     time: int|null
 * }
 */
class Event
{
    public ?string $eventType;
    /**
     * @var array<mixed>|null
     */
    public ?array $eventProperties;
    /**
     * @var array<mixed>|null
     */
    public ?array $userProperties;
    public ?string $userId;
    public ?string $deviceId;
    public ?string $insertId;
    public ?int $time;

    /**
     * @param array<mixed>|null $eventProperties
     * @param array<mixed>|null $userProperties
     */
    public function __construct(
        string $eventType,
        ?array $eventProperties = null,
        ?array $userProperties = null,
        ?string $userId = null,
        ?string $deviceId = null,
        ?string $insertId = null,
        ?int $time = null
    )
    {
        $this->eventType = $eventType;
        $this->eventProperties = $eventProperties;
        $this->userProperties = $userProperties;
        $this->userId = $userId;
        $this->deviceId = $deviceId;
        $this->insertId = $insertId;
        $this->time = $time;
    }

    /**
     * @return Payload
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
            'time' => $this->time
        ]);
    }

    /**
     * @param Payload $data
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            $data['event_type'],
            $data['event_properties'] ?? null,
            $data['user_properties'] ?? null,
            $data['user_id'] ?? null,
            $data['device_id'] ?? null,
            $data['insert_id'] ?? null,
            $data['time'] ?? null
        );
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
