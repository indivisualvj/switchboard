<?php

namespace App\Manager;

use App\Factory\NormalizerFactory;
use App\Normalizer\NormalizerInterface;

class NormalizerManager
{
    public function __construct(
        private readonly NormalizerFactory $normalizerFactory,
    ) {}

    public function normalize(array $config, $value, array $values)
    {
        $normalizers = $this->normalizerFactory->createAll($config);

        /** @var NormalizerInterface $normalizer */
        foreach ($normalizers as $normalizer) {
            $value = $normalizer->normalize($value, $values);
        }

        return trim($value);
    }
}
