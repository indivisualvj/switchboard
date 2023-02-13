<?php

namespace App\Normalizer;

use App\Application;

class InputOperationNormalizer extends AbstractNormalizer
{
    const OPERATOR_ADD = '+';
    const OPERATOR_SUB = '-';

    public function normalize($value, array $values)
    {
        if (self::OPERATOR_ADD === $this->getOperator()) {
            return $value + $values[$this->getInput()];

        } else if (self::OPERATOR_SUB === $this->getOperator()) {
            return $value - $values[$this->getInput()];
        }

        return null;
    }

    private function getInput(): string
    {
        return $this->config['input'];
    }

    private function getOperator(): string
    {
        return $this->config['operator'];
    }
}
