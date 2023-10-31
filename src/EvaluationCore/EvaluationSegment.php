<?php

namespace AmplitudeExperiment\EvaluationCore;

class EvaluationSegment
{
    public ?EvaluationBucket $bucket; // An optional EvaluationBucket object
    public ?array $conditions; // An array of arrays of EvaluationCondition objects
    public ?string $variant; // Optional string
    public ?array $metadata; // An associative array with keys as strings and values of any type
}
