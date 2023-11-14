<?php

namespace AmplitudeExperiment\Analytics;

class BaseEvent
{
    public ?string $eventType = null;
    public ?array $eventProperties = null;
    public ?array $userProperties = null;
    public ?array $groupProperties = null;
    public ?array $groups = null;
    public ?string $userId = null;
    public ?string $deviceId = null;
    public ?int $time = null;
    public ?float $locationLat = null;
    public ?float $locationLng = null;
    public ?string $appVersion = null;
    public ?string $versionName = null;
    public ?string $library = null;
    public ?string $platform = null;
    public ?string $osName = null;
    public ?string $osVersion = null;
    public ?string $deviceBrand = null;
    public ?string $deviceManufacturer = null;
    public ?string $deviceModel = null;
    public ?string $carrier = null;
    public ?string $country = null;
    public ?string $region = null;
    public ?string $city = null;
    public ?string $dma = null;
    public ?string $idfa = null;
    public ?string $idfv = null;
    public ?string $adid = null;
    public ?string $androidId = null;
    public ?string $language = null;
    public ?string $ip = null;
    public ?float $price = null;
    public ?int $quantity = null;
    public ?float $revenue = null;
    public ?string $productId = null;
    public ?string $revenueType = null;
    public ?int $eventId = null;
    public ?int $sessionId = null;
    public ?string $insertId = null;
    public ?string $partnerId = null;
    public ?string $userAgent = null;
    public ?string $androidAppSetId = null;
    public ?array $extra = null;

    public function __construct(string $eventType)
    {
        $this->eventType = $eventType;
    }
}
