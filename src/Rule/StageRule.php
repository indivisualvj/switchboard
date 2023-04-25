<?php

namespace App\Rule;

use Symfony\Component\Console\Output\OutputInterface;

class StageRule extends AbstractRule
{
    private static int $currentStage = 0;

    public function evaluate($value, ?OutputInterface $output = null): bool
    {
        $stages = $this->getStages();
        $stageValue = $this->getStageValue();
        if ($value > 0) {
            if ($value - $stageValue > 0) {
                self::$currentStage++;
                self::$currentStage = min($stages, self::$currentStage);
            }

        } else {
            self::$currentStage--;
            self::$currentStage = max(0, self::$currentStage);
        }

        return $this->lastStatus = self::$currentStage > 0;
    }

    public function result($value): string
    {
        $stageValue = $this->getStageValue();
        $result = is_numeric($value) && is_numeric($stageValue) ? $value-$stageValue : 'n/a';
        return sprintf('%s (%s - %s = %s) stage %s', $this->getInputKey(), $value, $stageValue, $result, self::$currentStage);
    }

    private function getStages(): int
    {
        return $this->config['stages'];
    }

    private function getStageValue(): int
    {
        return $this->config['stage_value'];
    }

    public function getTrueOutputs(): array
    {
        $result = [];
        $outputs = parent::getTrueOutputs();
        foreach ($outputs as $index => $output) {
            if ($index+1 <= self::$currentStage) {
                $result[] = $output;
            }
        }
        $outputs = $this->getFalseOutputs();
        foreach ($outputs as $index => $output) {
            if ($index+1 > self::$currentStage) {
                $result[] = $output;
            }
        }

        return $result;
    }

}
