<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationSegment
{
    /** @var EvaluationBucket|null */
    public ?EvaluationBucket $bucket;

    /** @var array<array<EvaluationCondition>>|null */
    public ?array $conditions;

    /** @var string|null */
    public ?string $variant;

    /** @var array|null */
    public ?array $metadata;

    /**
     * @param EvaluationBucket|null $bucket
     * @param array<array<EvaluationCondition>>|null $conditions
     * @param string|null $variant
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        ?EvaluationBucket $bucket = null,
        ?array $conditions = null,
        ?string $variant = null,
        ?array $metadata = null
    ) {
        $this->bucket = $bucket;
        $this->conditions = $conditions;
        $this->variant = $variant;
        $this->metadata = $metadata;
    }
}
