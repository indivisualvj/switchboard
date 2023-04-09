<?php

namespace App\Normalizer;

class FuzzyJsonNormalizer extends AbstractNormalizer
{
    public function normalize($value, array $values)
    {
        if (is_string($value)) {
            $start = strpos($value, '{');
            $end = strrpos($value, '}');
            $end = $end-$start+1;
            $value = substr($value, $start, $end);
            $value = str_replace('\'', '"', $value);
            $result = json_decode($value, true);
            if ($this->getKey() && isset($result[$this->getKey()])) {
                return $result[$this->getKey()];
            }
        }

        return null;
    }

    private function getKey(): ?string
    {
        return $this->config['key'] ?? null;
    }

}
