<?php

namespace App\Normalizer;

class RegexpNormalizer extends AbstractNormalizer
{
    public function normalize($value)
    {
        return preg_replace($this->getPattern(), $this->getReplacement(), $value);
    }

    private function getPattern(): ?string
    {
        return $this->config['pattern'] ?? null;
    }

    private function getReplacement(): ?string
    {
        return $this->config['replacement'] ?? null;
    }
}
