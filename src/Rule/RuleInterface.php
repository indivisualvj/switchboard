<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

interface RuleInterface
{
    public function execute($value, OutputInterface $output): array;
    public function evaluate($value, ?OutputInterface $output = null): bool;
    public function result($value): string;
    public function getInputKey(): ?string;
    public function getTrueOutputs(): array;
    public function getFalseOutputs(): array;
    public function getLastStatus(): bool;

}
