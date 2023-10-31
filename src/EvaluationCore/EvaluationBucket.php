<?php

namespace AmplitudeExperiment\EvaluationCore;

class EvaluationBucket
{
    public array $selector; // An array of strings
    public string $salt;
    public array $allocations; // An array of EvaluationAllocation objects
}
