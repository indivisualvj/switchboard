<?php

namespace App\Input;

use Symfony\Component\Console\Output\OutputInterface;

class AbstractInput implements InputInterface
{
    public function __construct(
        protected readonly array $config,
    )
    {
    }


    public function read(?OutputInterface $output)
    {
        return null;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
