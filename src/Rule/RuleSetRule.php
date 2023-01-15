<?php

namespace App\Rule;

class RuleSetRule extends AbstractRule implements RuleSetInterface
{
    const AND = 'AND';
    const OR = 'OR';
    const XOR = 'XOR';

    private array $rules = [];

    public function execute($value): bool
    {
        return $this->evaluate($value);
    }

    public function reason($value): string
    {
        $reasons = [];
        /** @var RuleInterface $rule */
        foreach ($this->rules as $key => $rule) {
            $reasons[] = $rule->reason($value[$rule->getInputKey()]);
        }

        return sprintf('%s', implode(' ' . $this->getOperator() . ' ', $reasons));
    }

    private function evaluate($value): bool
    {
        $operator = $this->getOperator();

        $conclusion = false;

        /** @var RuleInterface $rule */
        foreach ($this->rules as $rule) {
            $inputKey = $rule->getInputKey();
            $result = $rule->execute($value[$inputKey]);

            switch ($operator) {
                case self::AND:
                    if (!$result) {
                        return false;
                    }
                    $conclusion = true;
                    break;
                case self::OR:
                    if ($result) {
                        return true;
                    }
                    break;
                case self::XOR:
                    if ($result && $conclusion) {
                        return false;
                    }
                    $conclusion = $result;
                    break;
            }
        }

        return $conclusion;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    private function getOperator(): string
    {
        return strtoupper($this->config['operator']);
    }

}
