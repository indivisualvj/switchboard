<?php

namespace App\Factory;

use App\Rule\RuleInterface;
use App\Rule\RuleSetInterface;
use App\Util\StringUtil;

class RuleFactory
{
    public function createNew($name, $config): RuleInterface
    {
        $className = StringUtil::createClassName($config['type'], 'App\Rule\\', 'Rule');

        $rule = new $className($name, $config);

        if ($rule instanceof RuleSetInterface) {
            $rule->setRules($this->createAll($config['rules']));
        }

        return $rule;
    }

    public function createAll($rules): array
    {
        $result = [];
        foreach ($rules as $key => $config) {
            $result[$key] = $this->createNew($key, $config);
        }

        return $result;
    }
}
