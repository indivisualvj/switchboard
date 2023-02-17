<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

class ComparisonRule extends AbstractRule
{
    const GREATER = 'GREATER';
    const SMALLER = 'SMALLER';
    const EQUAL = 'EQUAL';

    public function reason($value): string
    {
        $result = $this->evaluate($value);
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

    public function evaluate($value, ?OutputInterface $output = null): bool
    {
        $operator = $this->getOperator();
        $operand = $this->getValue();
        switch ($operator) {
            case self::SMALLER:
                return $value < $operand;
            case self::GREATER:
                return $value > $operand;
            case self::EQUAL:
                return $value == $operand;
        }
    }
}
