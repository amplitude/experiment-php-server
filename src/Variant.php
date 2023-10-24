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
    public ?string $payload;

    public function __construct(
        ?string $value = null,
        ?string $payload = null
    ) {
        $this->value = $value;
        $this->payload = $payload;
    }
}
