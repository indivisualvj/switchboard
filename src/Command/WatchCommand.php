<?php

namespace App\Command;

use App\Manager\InputManager;
use App\Manager\OutputManager;
use App\Manager\RuleManager;
use App\Rule\RuleInterface;
use App\SubRoutine\RunSubRoutine;
use App\SubRoutine\TerminateSubRoutine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command implements SignalableCommandInterface
{
    private $terminated = false;

    public function __construct(
        private readonly RunSubRoutine $runSubRoutine,
        private readonly TerminateSubRoutine $terminateSubRoutine,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('watch')
            ->addArgument('interval', InputArgument::REQUIRED, 'Interval in seconds')
            ->setDescription('Run application')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        while (!$this->terminated) {
            $this->runSubRoutine->execute($input, $output);
            sleep($input->getArgument('interval'));
        }

        $output->writeln('received signal to terminate');
        $this->terminateSubRoutine->execute($input, $output);

        return 0;
    }

    public function getSubscribedSignals(): array
    {
        // return here any of the constants defined by PCNTL extension
        // https://www.php.net/manual/en/pcntl.constants.php
        return [SIGINT, SIGTERM, SIGABRT, SIGTSTP, SIGQUIT];
    }

    public function handleSignal(int $signal): void
    {
        $this->terminated = true;
    }
}
