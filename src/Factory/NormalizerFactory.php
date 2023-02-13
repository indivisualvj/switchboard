<?php

namespace App\Factory;

use App\Normalizer\NormalizerInterface;
use App\Util\StringUtil;

class NormalizerFactory
{
    public function createNew($key, $config): NormalizerInterface
    {
            $className = StringUtil::createClassName($key, 'App\Normalizer\\', 'Normalizer');
            return new $className($config);
    }

    public function createAll(array $config): array
    {
        $normalizers = [];
        foreach ($config as $key => $options) {
            $normalizers[$key] = $this->createNew($key, $options);
        }

        return $normalizers;
    }
}
