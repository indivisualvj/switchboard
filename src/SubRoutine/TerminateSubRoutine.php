<?php

namespace App\SubRoutine;

use App\Manager\OutputManager;
use App\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class TerminateSubRoutine implements SubRoutineInterface
{
    public function __construct(
        private readonly OutputManager$outputManager,
    ) {
    }

    const LINE_LENGTH = 80;

    public function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $outputs = $this->outputManager->getOutputs();

        /** @var OutputInterface $outputCommand */
        foreach ($outputs as $outputCommand) {
            if ($outputCommand->runOnTermSignal()) {
                $outputCommand->write($output);
            }
        }

        return 0;
    }
}
