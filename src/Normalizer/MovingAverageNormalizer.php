<?php

namespace App\Normalizer;

use App\Application;

class MovingAverageNormalizer extends AbstractNormalizer
{
    public function normalize($value)
    {
        $values = $this->getValues();
        $values[] = $value;
        $this->writeValues($values);

        return array_sum($values) / count($values);

    }

    private function getValues(): array
    {
        $file = $this->getFile();
        if (!file_exists($file)) {
            $dir = substr($file, 0, strlen($file)-strlen(basename($file)));
            mkdir($dir, 0755, true);

            $db = ['values' => []];
        } else {
            $db = json_decode(file_get_contents($file), true);
        }
        $values = $db['values'];
        $numValues = count($values);
        if ($numValues >= $this->getNumValues()) {
            $length = $this->getNumValues()-1;
            $offset = $numValues - $length;
            $values = array_slice($values, $offset);
        }

        return $values;
    }

    private function writeValues($values): void
    {
        $file = $this->getFile();
        file_put_contents($file, json_encode(['values' => $values]));
    }

    private function getFile(): string
    {
        return sprintf('%s/%s', Application::getBaseDir(), $this->config['file']);
    }

    private function getNumValues(): int
    {
        return $this->config['num_values'];
    }
}
