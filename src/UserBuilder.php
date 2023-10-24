<?php

namespace AmplitudeExperiment;

class UserBuilder
{
    protected ?string $deviceId = null;
    protected ?string $userId = null;
    protected ?string $country = null;
    protected ?string $city = null;
    protected ?string $region = null;
    protected ?string $dma = null;
    protected ?string $language = null;
    protected ?string $platform = null;
    protected ?string $version = null;
    protected ?string $os = null;
    protected ?string $deviceManufacturer = null;
    protected ?string $deviceBrand = null;
    protected ?string $deviceModel = null;
    protected ?string $carrier = null;
    protected ?string $library = null;
    protected ?array $userProperties = null;
    protected ?array $groups = null;
    protected ?array $groupProperties = null;

    public function __construct()
    {
    }

    public function deviceId(?string $deviceId): UserBuilder
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    public function userId(?string $userId): UserBuilder
    {
        $this->userId = $userId;
        return $this;
    }

    public function country(?string $country): UserBuilder
    {
        $this->country = $country;
        return $this;
    }

    public function city(?string $city): UserBuilder
    {
        $this->city = $city;
        return $this;
    }

    public function region(?string $region): UserBuilder
    {
        $this->region = $region;
        return $this;
    }

    public function dma(?string $dma): UserBuilder
    {
        $this->dma = $dma;
        return $this;
    }

    public function language(?string $language): UserBuilder
    {
        $this->language = $language;
        return $this;
    }

    public function platform(?string $platform): UserBuilder
    {
        $this->platform = $platform;
        return $this;
    }

    public function version(?string $version): UserBuilder
    {
        $this->version = $version;
        return $this;
    }

    public function os(?string $os): UserBuilder
    {
        $this->os = $os;
        return $this;
    }

    public function deviceManufacturer(?string $deviceManufacturer): UserBuilder
    {
        $this->deviceManufacturer = $deviceManufacturer;
        return $this;
    }

    public function deviceBrand(?string $deviceBrand): UserBuilder
    {
        $this->deviceBrand = $deviceBrand;
        return $this;
    }

    public function deviceModel(?string $deviceModel): UserBuilder
    {
        $this->deviceModel = $deviceModel;
        return $this;
    }

    public function carrier(?string $carrier): UserBuilder
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function library(?string $library): UserBuilder
    {
        $this->library = $library;
        return $this;
    }

    public function userProperties(?array $userProperties): UserBuilder
    {
        $this->userProperties = $userProperties;
        return $this;
    }

    public function groups(?array $groups): UserBuilder
    {
        $this->groups = $groups;
        return $this;
    }

    public function groupProperties(?array $groupProperties): UserBuilder
    {
        $this->groupProperties = $groupProperties;
        return $this;
    }

    public function build(): User
    {
        return new User(
            $this->deviceId,
            $this->userId,
            $this->country,
            $this->city,
            $this->region,
            $this->dma,
            $this->language,
            $this->platform,
            $this->version,
            $this->os,
            $this->deviceManufacturer,
            $this->deviceBrand,
            $this->deviceModel,
            $this->carrier,
            $this->library,
            $this->userProperties,
            $this->groups,
            $this->groupProperties
        );
    }
}
