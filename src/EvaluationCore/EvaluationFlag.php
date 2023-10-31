<?php

namespace AmplitudeExperiment\EvaluationCore;
class EvaluationFlag {
    public string $key;
    public array $variants; // An array of EvaluationVariant objects
    public array $segments; // An array of EvaluationSegment objects
    public ?array $dependencies; // An array of string values or null
    public ?array $metadata; // An associative array with keys as strings and values of any type
}
