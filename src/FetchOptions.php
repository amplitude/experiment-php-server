<?php

namespace AmplitudeExperiment;

/**
 * Options to modify the behavior of a remote evaluation fetch request.
 */
class FetchOptions
{
    /**
     * Specific flag keys to evaluate and set variants for.
     *
     * @var string[]
     */
    public array $flagKeys;

    /**
     * FetchOptions constructor.
     *
     * @param string[] $flagKeys Specific flag keys to evaluate and set variants for.
     */
    public function __construct(array $flagKeys)
    {
        $this->flagKeys = $flagKeys;
    }
}
