<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationFlag
{
    /** @var array|null */
    public ?array $metadata = null;

    /** @var array<string, EvaluationVariant> */
    public array $variants;

    /** @var string */
    public string $key;

    /** @var EvaluationSegment[] */
    public array $segments;

    /** @var array|null */
    public ?array $dependencies = null;

    /**
     * @param string $key
     * @param array<string, EvaluationVariant> $variants
     * @param EvaluationSegment[] $segments
     * @param array<string>|null $dependencies
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $key,
        array  $variants,
        array  $segments,
        ?array $dependencies = null,
        ?array $metadata = null
    ) {
        $this->dependencies = $dependencies;
        $this->segments = $segments;
        $this->key = $key;
        $this->variants = $variants;
        $this->metadata = $metadata;
    }
}
