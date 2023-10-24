<?php

namespace AmplitudeExperiment\Local;

class LocalEvaluationConfig
{
    public ?bool $debug;
    public ?string $serverUrl;
    public ?array $bootstrap;
    public ?int $flagConfigPollingIntervalMillis;

    public function __construct(?bool
}
