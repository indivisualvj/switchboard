<?php

namespace App\Manager;

use App\Factory\OutputFactory;
use App\Output\OutputInterface;

class OutputManager
{
    private array $outputs = [];

    public function __construct(
        array $outputs,
        private readonly OutputFactory $outputFactory,
    ) {
        foreach ($outputs as $key => $config) {
            $this->outputs[$key] = $this->outputFactory->createNew($config);
        }
    }

    public function getOutputs(): array
    {
        return $this->outputs;
    }

    public function getOutput(string $key): OutputInterface
    {
        return $this->outputs[$key];
    }
}
