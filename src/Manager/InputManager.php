<?php

namespace App\Manager;

use App\Factory\InputFactory;
use App\Input\InputInterface;

class InputManager
{
    private array $inputs = [];

    public function __construct(
        array $inputs,
        private readonly InputFactory $inputFactory,
    ) {
        foreach ($inputs as $key => $config) {
            $this->inputs[$key] = $this->inputFactory->createNew($config);
        }
    }


    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function getInput(string $key): InputInterface
    {
        return $this->inputs[$key];
    }
}
