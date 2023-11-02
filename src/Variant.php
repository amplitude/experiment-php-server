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

    public function __construct(
        ?string $value = null,
        $payload = null
    ) {
        $this->value = $value;
        $this->payload = $payload;
    }
}
