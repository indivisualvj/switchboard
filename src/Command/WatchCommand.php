<?php

namespace App\Command;

use App\Manager\RuleManager;
use App\SubRoutine\RunSubRoutine;
use App\SubRoutine\TerminateSubRoutine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command implements SignalableCommandInterface
{
    private $terminated = false;

    public function __construct(
        private readonly RunSubRoutine $runSubRoutine,
        private readonly TerminateSubRoutine $terminateSubRoutine,
        private readonly string $kernelProjectDir,
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
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->section();
        }

        while (!$this->terminated && !$this->checkTerminated()) {
            // add logging
            $output->clear();
            $this->runSubRoutine->execute($input, $output);
            sleep($input->getArgument('interval'));
        }

        $output->writeln(sprintf('received signal to terminate (%d)', $this->terminated));
        $this->terminateSubRoutine->execute($input, $output);

        return 0;
    }

    private function checkTerminated(): bool {
        $filename = $this->kernelProjectDir . '/terminate';
        if (file_exists($filename)) {
            $term = file_get_contents($filename);
            if ($term) {
                file_put_contents($filename, '0');
                return true;
            }
        }
        return false;
    }

    public function getSubscribedSignals(): array
    {
        // return here any of the constants defined by PCNTL extension
        // https://www.php.net/manual/en/pcntl.constants.php
        return [SIGINT, SIGTERM, SIGABRT, SIGTSTP, SIGQUIT];
    }

    public function handleSignal(int $signal): void
    {
        $this->terminated = $signal;
    }
}
