<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationAllocation
{
    /** @var array<int> */
    public array $range;

    /** @var EvaluationDistribution[] */
    public array $distributions;

    /**
     * @param array<int> $range
     * @param EvaluationDistribution[] $distributions
     */
    public function __construct(
        array $range,
        array $distributions
    ) {
        $this->range = $range;
        $this->distributions = $distributions;
    }
}
