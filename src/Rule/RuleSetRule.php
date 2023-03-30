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

    public function result($value): string
    {
        $reasons = [];
        /** @var RuleInterface $rule */
        foreach ($this->getRules() as $key => $rule) {
            $reasons[] = $rule->result($value[$rule->getInputKey()] ?? $value);
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
            $result = $rule->evaluate($value[$inputKey] ?? $value, $output);

            if ($result) {
                $this->trueRules[] = $rule;
            } else {
                $this->falseRules[] = $rule;
            }

            switch ($operator) {
                case self::AND:
                    if (!$result) {
                        return $this->lastStatus = false;
                    }
                    $conclusion = true;
                    break;
                case self::OR:
                    if ($result) {
                        return $this->lastStatus = true;
                    }
                    break;
                case self::XOR:
                    if ($result && $conclusion) {
                        return $this->lastStatus = false;
                    }
                    $conclusion = $result;
                    break;
            }
        }

        return $this->lastStatus = $conclusion;
    }

    private function getOperator(): string
    {
        return strtoupper($this->config['operator']);
    }

    public function getTrueOutputs(): array
    {
        $outputs = parent::getTrueOutputs();

        if (!count($outputs)) {
            /** @var RuleInterface $rule */
            foreach ($this->trueRules as $rule) {
                $outputs = array_merge($outputs, $rule->getTrueOutputs());
            }
        }

        return $outputs;
    }

    public function getFalseOutputs(): array
    {
        $outputs = parent::getFalseOutputs();

        if (!count($outputs)) {
            /** @var RuleInterface $rule */
            foreach ($this->falseRules as $rule) {
                $outputs = array_merge($outputs, $rule->getFalseOutputs());
            }
        }

        return $outputs;
    }

}
