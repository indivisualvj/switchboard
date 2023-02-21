<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

class AbstractRule implements RuleInterface
{
    public function __construct(
        protected string $name,
        protected array $config,
    ) {

    }

    public function execute($value, ?OutputInterface $output = null): array
    {
        $result = $this->evaluate($value, $output);

        if ($result) {
            $outputKeys = $this->getTrueOutputs();

        } else {
            $outputKeys = $this->getFalseOutputs();
        }

        return $outputKeys;
    }

    public function evaluate($value, ?OutputInterface $output = null): bool
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

    public function result($value): string
    {
        return 'n/a';
    }
}
