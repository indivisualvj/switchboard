<?php

namespace App\Rule;

interface RuleInterface
{
    public function execute($value): bool;
    public function reason($value): string;
    public function getInputKey(): ?string;
    public function getTrueOutputs(): array;
    public function getFalseOutputs(): array;

}
