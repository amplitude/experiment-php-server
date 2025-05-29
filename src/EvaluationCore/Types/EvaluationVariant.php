<?php
namespace AmplitudeExperiment\EvaluationCore\Types;

class EvaluationVariant {
    public ?string $key;
    public $value;
    public $payload;
    public ?array $metadata;

    public function __construct(?string $key = null, $value = null, $payload = null, ?array $metadata = null) {
        $this->key = $key;
        $this->value = $value;
        $this->payload = $payload;
        $this->metadata = $metadata;
    }

    /**
     * Creates an EvaluationVariant from a JSON response body
     *
     * @param array $data The decoded JSON data
     * @return EvaluationVariant
     */
    public static function fromJson(array $data): EvaluationVariant {
        return new self(
            $data['key'] ?? null,
            $data['value'] ?? null,
            $data['payload'] ?? null,
            $data['metadata'] ?? null
        );
    }

    /**
     * Creates an array of EvaluationVariants from evaluation results
     *
     * @param array $results The evaluation results from response body
     * @return array<string, EvaluationVariant>
     */
    public static function fromEvaluationResults(array $results): array {
        $variants = [];
        foreach ($results as $flagKey => $variantData) {
            $variants[$flagKey] = self::fromJson($variantData);
        }
        return $variants;
    }
}
