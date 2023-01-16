<?php

namespace App\Command;

use App\SubRoutine\RunSubRoutine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{

    public function __construct(
        private readonly RunSubRoutine $runSubRoutine,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run application')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->runSubRoutine->execute($input, $output);
    }
}
