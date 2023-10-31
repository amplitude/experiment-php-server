<?php

namespace AmplitudeExperiment\EvaluationCore;

class EvaluationVariant
{
    public ?string $key; // Optional string
    public $value; // Any type
    public $payload; // Any type
    public ?array $metadata; // An associative array with keys as strings and values of any type
}
