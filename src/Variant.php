<?php

namespace AmplitudeExperiment;

use AmplitudeExperiment\EvaluationCore\Types\EvaluationVariant;

class Variant
{
    /**
     * The key of the variant.
     */
    public ?string $key;
    /**
     * The value of the variant determined by the flag configuration
     */
    public ?string $value;
    /**
     * @var mixed
     * The attached payload, if any
     */
    public $payload;
    /**
     * The experiment key. Used to distinguish two experiments associated with the same flag.
     */
    public ?string $expKey;
    /**
     * @var ?array<mixed>
     * Flag, segment, and variant metadata produced as a result of
     * evaluation for the user. Used for system purposes.
     */
    public ?array $metadata;

    /**
     * @param mixed $payload
     * @param ?array<mixed> $metadata
     */
    public function __construct(
        ?string $key = null,
        ?string $value = null,
                $payload = null,
        ?string $expKey = null,
        ?array  $metadata = null
    )
    {
        $this->key = $key;
        $this->value = $value;
        $this->payload = $payload;
        $this->expKey = $expKey;
        $this->metadata = $metadata;
    }

    /**
     * Converts an EvaluationVariant to a public Variant
     *
     * @param EvaluationVariant $evaluationVariant The evaluation variant to convert
     * @return Variant The converted variant
     */
    public static function convertEvaluationVariantToVariant(EvaluationVariant $evaluationVariant): Variant
    {
        $variant = new Variant();

        $experimentKey = null;

        if (isset($evaluationVariant->metadata)) {
            $experimentKey = $evaluationVariant->metadata['experimentKey'] ?? null;
        }

        if ($evaluationVariant->key !== null) {
            $variant->key = $evaluationVariant->key;
        }

        if ($evaluationVariant->value !== null) {
            $variant->value = (string)$evaluationVariant->value;
        }

        if ($evaluationVariant->payload !== null) {
            $variant->payload = $evaluationVariant->payload;
        }

        if ($experimentKey !== null) {
            $variant->expKey = $experimentKey;
        }

        if (isset($evaluationVariant->metadata)) {
            $variant->metadata = $evaluationVariant->metadata;
        }

        return $variant;
    }
}
