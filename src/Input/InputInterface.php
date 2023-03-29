<?php

namespace App\Input;

use Symfony\Component\Console\Output\OutputInterface;

interface InputInterface
{
    public function read(?OutputInterface $output);

    public function getConfig(): array;

    public function getDefault();
}
