<?php

namespace AmplitudeExperiment;

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
     * The attached payload, if any
     */
    public $payload;
    /**
     * The experiment key. Used to distinguish two experiments associated with the same flag.
     */
    public ?string $expKey;
    /**
     * Flag, segment, and variant metadata produced as a result of
     * evaluation for the user. Used for system purposes.
     */
    public ?array $metadata;

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

    public static function convertEvaluationVariantToVariant(array $evaluationVariant): Variant
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
