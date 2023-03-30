<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

class BooleanRule extends AbstractRule
{
    public function result($value): string
    {
        $result = $this->evaluate($value);
        return sprintf('%s from %s equals %s (%s)', $value?'true':'false', $this->getInputKey(), $this->getValue()?'true':'false', $result?'true':'false');
    }

    private function getValue(): string
    {
        return $this->config['value'];
    }

    public function evaluate($value, ?OutputInterface $output = null): bool
    {
        return $this->lastStatus = $value == $this->getValue();
    }
}
