<?php

namespace App\Rule;

use App\Factory\RuleFactory;

interface RuleSetInterface
{
    public function setRules(array $rules): void;
    public function getRules(): array;
}
