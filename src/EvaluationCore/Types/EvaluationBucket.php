<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationBucket
{
    /** @var array<string> */
    public array $selector;

    /** @var string */
    public string $salt;

    /** @var EvaluationAllocation[] */
    public array $allocations;

    /**
     * @param array<string> $selector
     * @param string $salt
     * @param EvaluationAllocation[] $allocations
     */
    public function __construct(
        array $selector,
        string $salt,
        array $allocations
    ) {
        $this->selector = $selector;
        $this->salt = $salt;
        $this->allocations = $allocations;
    }
}
