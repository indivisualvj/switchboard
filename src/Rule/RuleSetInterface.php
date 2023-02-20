<?php

namespace App\Rule;

interface RuleSetInterface
{
    public function setRules(array $rules): void;
    public function getRules(): array;
}
