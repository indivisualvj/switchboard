<?php

namespace App\Factory;

use App\Output\OutputInterface;
use App\Util\StringUtil;

class OutputFactory
{
    public function createNew(array $config): OutputInterface
    {
        $className = StringUtil::createClassName($config['type'], 'App\Output\\', 'Output');

        return new $className($config['command'], $config);
    }
}
