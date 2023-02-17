<?php

namespace App\Rule;

use App\Factory\RuleFactory;

class AbstractRuleSet extends AbstractRule implements RuleSetInterface
{
    private array $rules = [];

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }
}
