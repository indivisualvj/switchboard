<?php

namespace App\Rule;

class ComparisonRule extends AbstractRule
{
    const GREATER = 'GREATER';
    const SMALLER = 'SMALLER';
    const EQUAL = 'EQUAL';

    public function execute($value): bool
    {
        return $this->evaluate($value, $this->getOperator(), $this->getValue());
    }

    public function reason($value): string
    {
        $result = $this->evaluate($value, $this->getOperator(), $this->getValue());
        return sprintf('%s from %s is %s %s (%s)', $value, $this->getInputKey(), $this->getOperator(), $this->getValue(), $result?'true':'false');
    }

    private function getOperator(): string
    {
        return strtoupper($this->config['operator']);
    }

    private function getValue(): string
    {
        return $this->config['value'];
    }

    private function evaluate($valueA, $operator, $valueB): bool
    {
        switch ($operator) {
            case self::SMALLER:
                return $valueA < $valueB;
            case self::GREATER:
                return $valueA > $valueB;
            case self::EQUAL:
                return $valueA == $valueB;
        }
    }
}
