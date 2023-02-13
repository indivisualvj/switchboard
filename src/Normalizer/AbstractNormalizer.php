<?php

namespace App\Normalizer;

class AbstractNormalizer implements NormalizerInterface
{
    public function __construct(
        protected readonly array $config,
    ) {

    }

    public function normalize($value, array $values)
    {
        return $value;
    }
}
