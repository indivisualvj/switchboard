<?php

namespace App\Normalizer;

class ImplausibleValueNormalizer extends AbstractNormalizer
{
    const GREATER = 'GREATER';
    const SMALLER = 'SMALLER';
    const EQUAL = 'EQUAL';

    public function normalize($value, array $values)
    {
        $operand = $this->getOperand();

        if (self::GREATER === $this->getOperator()) {
            return $value > $operand ? $this->getFallback() : $value;

        } else if (self::SMALLER === $this->getOperator()) {
            return $value < $operand ? $this->getFallback() : $value;

        } else if (self::EQUAL === $this->getOperator()) {
            return $value === $operand ? $this->getFallback() : $value;
        }

        return $value;
    }

    private function getOperand(): string
    {
        return $this->config['operand'];
    }

    private function getOperator(): string
    {
        return strtoupper($this->config['operator']);
    }

    private function getFallback(): string
    {
        return $this->config['fallback'];
    }
}
