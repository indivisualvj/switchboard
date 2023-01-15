<?php

namespace App\Factory;

use App\Input\InputInterface;
use App\Util\StringUtil;

class InputFactory
{
    public function __construct(
        private readonly NormalizerFactory $normalizerFactory,
    ) {

    }

    public function createNew(array $config): InputInterface
    {
        $className = StringUtil::createClassName($config['type'], 'App\Input\\', 'Input');
        $normalizers = $this->normalizerFactory->createAll($config);

        return new $className($config['command'], $normalizers, $config);
    }

}
