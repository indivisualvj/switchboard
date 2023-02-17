<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

class RuleSetRule extends AbstractRuleSet
{
    const AND = 'AND';
    const OR = 'OR';
    const XOR = 'XOR';

    private array $trueRules;
    private array $falseRules;

    public function reason($value): string
    {
        $reasons = [];
        /** @var RuleInterface $rule */
        foreach ($this->getRules() as $key => $rule) {
            $reasons[] = $rule->reason($value[$rule->getInputKey()] ?? $value);
        }

        return sprintf('%s', implode(' ' . $this->getOperator() . ' ', $reasons));
    }

    public function evaluate($value, ?OutputInterface $output = null): bool
    {
        $operator = $this->getOperator();

        $conclusion = false;
        $this->trueRules = [];
        $this->falseRules = [];

        /** @var RuleInterface $rule */
        foreach ($this->getRules() as $rule) {
            $inputKey = $rule->getInputKey();
            $result = $rule->execute($value[$inputKey] ?? $value, $output);

            if ($result) {
                $this->trueRules[] = $rule;
            } else {
                $this->falseRules[] = $rule;
            }

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

    private function getOperator(): string
    {
        return strtoupper($this->config['operator']);
    }

    public function getTrueOutputs(): array
    {
        $outputs = [];

        /** @var RuleInterface $rule */
        foreach ($this->trueRules as $rule) {
            $outputs = array_merge($outputs, $rule->getTrueOutputs());
        }

        return count($outputs) ? $outputs : parent::getTrueOutputs();
    }

    public function getFalseOutputs(): array
    {

        $outputs = [];

        /** @var RuleInterface $rule */
        foreach ($this->falseRules as $rule) {
            $outputs = array_merge($outputs, $rule->getFalseOutputs());
        }

        return count($outputs) ? $outputs : parent::getFalseOutputs();
    }

}
