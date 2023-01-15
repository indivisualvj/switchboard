<?php

namespace App\Rule;

class AbstractRule implements RuleInterface
{
    public function __construct(
        protected array $config,
    ) {

    }

    public function execute($value): bool
    {
        return false;
    }

    public function getInputKey(): ?string
    {
        return $this->config['input'] ?? null;
    }

    public function getTrueOutputs(): array
    {
        return $this->getOutputs('true');
    }

    public function getFalseOutputs(): array
    {
        return $this->getOutputs('false');
    }

    private function getOutputs($key): array
    {
        return $this->config['outputs'][$key] ?? [];
    }

    public function reason($value): string
    {
        return 'n/a';
    }
}
