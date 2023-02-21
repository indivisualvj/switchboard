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
        $output->writeln(sprintf('value: %s - %s is %d', $value, $stageValue, $value-$stageValue));

        if ($value > 0) {
            if ($value - $stageValue > 0) {
                self::$currentStage++;
                self::$currentStage = min($stages, self::$currentStage);
            }

        } else {
            self::$currentStage--;
            self::$currentStage = max(0, self::$currentStage);
        }

        $output->writeln('current stage is: ' . self::$currentStage);

        return true;
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
        return [];
    }

    public function getFalseOutputs(): array
    {
        return [];
    }
}
