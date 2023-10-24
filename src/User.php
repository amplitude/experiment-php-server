<?php

namespace AmplitudeExperiment;

/**
 * The user to fetch experiment/flag variants for. This is an immutable object
 * that can be created using an {@link UserBuilder}. Example usage:
 *
 * ```
 * User::builder()->userId("user@company.com")->build()
 * ```
 *
 * You can copy and modify a user using [copyToBuilder].
 *
 * ```
 * val user = User::builder()
 *     ->userId("user@company.com")
 *     ->build()
 * val newUser = user->copyToBuilder()
 *     ->userProperty("username", "bumblebee")
 *     ->build()
 * ```
 */
class User
{
    public ?string $deviceId;
    public ?string $userId;
    public ?string $country;
    public ?string $city;
    public ?string $region;
    public ?string $dma;
    public ?string $language;
    public ?string $platform;
    public ?string $version;
    public ?string $os;
    public ?string $deviceManufacturer;
    public ?string $deviceBrand;
    public ?string $deviceModel;
    public ?string $carrier;
    public ?string $library;
    public ?array $userProperties;
    public ?array $groups;
    public ?array $groupProperties;

    public function __construct(
        ?string $deviceId,
        ?string $userId,
        ?string $country,
        ?string $city,
        ?string $region,
        ?string $dma,
        ?string $language,
        ?string $platform,
        ?string $version,
        ?string $os,
        ?string $deviceManufacturer,
        ?string $deviceBrand,
        ?string $deviceModel,
        ?string $carrier,
        ?string $library,
        ?array  $userProperties,
        ?array  $groups,
        ?array  $groupProperties
    ) {
        $this->deviceId = $deviceId;
        $this->userId = $userId;
        $this->country = $country;
        $this->city = $city;
        $this->region = $region;
        $this->dma = $dma;
        $this->language = $language;
        $this->platform = $platform;
        $this->version = $version;
        $this->os = $os;
        $this->deviceManufacturer = $deviceManufacturer;
        $this->deviceBrand = $deviceBrand;
        $this->deviceModel = $deviceModel;
        $this->carrier = $carrier;
        $this->library = $library;
        $this->userProperties = $userProperties;
        $this->groups = $groups;
        $this->groupProperties = $groupProperties;
    }

    public static function builder(): UserBuilder {
        return new UserBuilder();
    }

    public function copyToBuilder(): UserBuilder
    {
        return (new UserBuilder())
            ->deviceId($this->deviceId)
            ->userId($this->userId)
            ->country($this->country)
            ->city($this->city)
            ->region($this->region)
            ->dma($this->dma)
            ->language($this->language)
            ->platform($this->platform)
            ->version($this->version)
            ->os($this->os)
            ->deviceManufacturer($this->deviceManufacturer)
            ->deviceBrand($this->deviceBrand)
            ->deviceModel($this->deviceModel)
            ->carrier($this->carrier)
            ->library($this->library)
            ->userProperties($this->userProperties)
            ->groups($this->groups)
            ->groupProperties($this->groupProperties);
    }
}
