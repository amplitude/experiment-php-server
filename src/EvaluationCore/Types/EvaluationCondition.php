<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationCondition
{
    /** @var array<string> */
    public array $selector;

    /** @var string */
    public string $op;

    /** @var array<string> */
    public array $values;

    /**
     * @param array<string> $selector
     * @param string $op
     * @param array<string> $values
     */
    public function __construct(
        array $selector,
        string $op,
        array $values
    ) {
        $this->selector = $selector;
        $this->op = $op;
        $this->values = $values;
    }
}
