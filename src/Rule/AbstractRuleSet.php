<?php

namespace App\Rule;

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
