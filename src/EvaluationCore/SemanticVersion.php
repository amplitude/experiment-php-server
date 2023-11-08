<?php

namespace AmplitudeExperiment\EvaluationCore;

class SemanticVersion
{
    public ?string $major;
    public ?string $minor;
    public ?string $patch;
    public ?string $preRelease;

    public function __construct(?string $major, ?string $minor, ?string $patch, ?string $preRelease = null)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->preRelease = $preRelease;
    }

    public static function parse(?string $version): ?SemanticVersion
    {
        if (!$version) {
            return null;
        }

        $matches = [];
        $pattern = '/^(\d+)\.(\d+)(\.(\d+))?(-[-\w]+(\.[-\w]+)*)?$/';
        if (!preg_match($pattern, $version, $matches)) {
            return null;
        }

        $major = (int)$matches[1];
        $minor = (int)$matches[2];
        if (!is_numeric($major) || !is_numeric($minor)) {
            return null;
        }

        $patch = isset($matches[4]) ? (int)$matches[4] : 0;
        $preRelease = $matches[5] ?? null;

        return new SemanticVersion($major, $minor, $patch, $preRelease);
    }

    public function compareTo(?SemanticVersion $other): int
    {
        if ($this->major > $other->major) return 1;
        if ($this->major < $other->major) return -1;
        if ($this->minor > $other->minor) return 1;
        if ($this->minor < $other->minor) return -1;
        if ($this->patch > $other->patch) return 1;
        if ($this->patch < $other->patch) return -1;
        if ($this->preRelease && !$other->preRelease) return -1;
        if (!$this->preRelease && $other->preRelease) return 1;
        if ($this->preRelease && $other->preRelease) {
            if ($this->preRelease > $other->preRelease) return 1;
            if ($this->preRelease < $other->preRelease) return -1;
            return 0;
        }
        return 0;
    }
}
