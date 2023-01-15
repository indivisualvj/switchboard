<?php

namespace App\Manager;

use App\Factory\RuleFactory;

class RuleManager
{
    private array $rules = [];

    public function __construct(
        array $rules,
        public readonly RuleFactory $ruleFactory,
    ) {
        $this->rules = $this->ruleFactory->createAll($rules);
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
