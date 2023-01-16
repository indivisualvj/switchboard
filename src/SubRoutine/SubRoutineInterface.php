<?php

namespace App\SubRoutine;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface SubRoutineInterface
{
    public function execute(InputInterface $input, OutputInterface $output): int;
}
