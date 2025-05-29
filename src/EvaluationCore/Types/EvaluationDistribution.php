<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationDistribution
{
    /** @var string */
    public string $variant;

    /** @var array<int> */
    public array $range;

    /**
     * @param string $variant
     * @param array<int> $range
     */
    public function __construct(
        string $variant,
        array $range
    ) {
        $this->variant = $variant;
        $this->range = $range;
    }
}
