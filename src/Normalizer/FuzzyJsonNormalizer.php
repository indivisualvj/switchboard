<?php

namespace App\Normalizer;

class FuzzyJsonNormalizer extends AbstractNormalizer
{
    public function normalize($value)
    {
        $result = null;
        if ($value) {
            $start = strpos($value, '{');
            $end = strrpos($value, '}');
            $end = $end-$start+1;
            $value = substr($value, $start, $end);

            $result = json_decode($value, true);
        }

        if ($this->getKey()) {
            return $result[$this->getKey()];
        }

        return $result;
    }

    private function getKey(): ?string
    {
        return $this->config['key'] ?? null;
    }

}
