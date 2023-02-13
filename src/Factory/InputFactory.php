<?php

namespace App\Factory;

use App\Input\InputInterface;
use App\Util\StringUtil;

class InputFactory
{
    public function createNew(array $config): InputInterface
    {
        $className = StringUtil::createClassName($config['type'], 'App\Input\\', 'Input');
        return new $className($config);
    }

}
