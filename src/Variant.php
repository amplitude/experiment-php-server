<?php

namespace AmplitudeExperiment;

class Variant
{
    /**
     * The value of the variant determined by the flag configuration
     */
    public ?string $value;
    /**
     * The attached payload, if any
     */
    public $payload;
    public ?array $metadata;
    public ?string $key;
    public ?string $expKey;

    public function __construct(
        ?string $value = null,
                $payload = null,
        ?array  $metadata = null,
        ?string $key = null,
        ?string $expKey = null
    )
    {
        $this->value = $value;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->key = $key;
        $this->expKey = $expKey;
    }

    public static function convertEvaluationVariantToVariant($evaluationVariant): Variant
    {

        $variant = new Variant();

        if (empty($evaluationVariant)) {
            return $variant;
        }

        $experimentKey = null;

        if (isset($evaluationVariant['metadata'])) {
            $experimentKey = $evaluationVariant['metadata']['experimentKey'] ?? null;
        }

        if (isset($evaluationVariant['key'])) {
            $variant->key = $evaluationVariant['key'];
        }

        if (isset($evaluationVariant['value'])) {
            $variant->value = (string)$evaluationVariant['value'];
        }

        if (isset($evaluationVariant['payload'])) {
            $variant->payload = $evaluationVariant['payload'];
        }

        if ($experimentKey) {
            $variant->expKey = $experimentKey;
        }

        if (isset($evaluationVariant['metadata'])) {
            $variant->metadata = $evaluationVariant['metadata'];
        }

        return $variant;
    }
}
