<?php

namespace AmplitudeExperiment\Local;

class LocalEvaluationClient
{
    private string $apiKey;
    private LocalEvaluationConfig $config;

    public function start()
    {

    }

    // TODO type check on map-array of variants returned?
    public function evaluate(User $user, array $flagKeys): array
    {
        return [];
    }

}
