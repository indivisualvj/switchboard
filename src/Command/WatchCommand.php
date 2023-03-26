<?php

namespace App\Command;

use App\Manager\RuleManager;
use App\SubRoutine\RunSubRoutine;
use App\SubRoutine\TerminateSubRoutine;
use Exception;
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

        $sleepTimout = $input->getArgument('interval') / 5;

        while (!$this->terminated) {
            $this->setStatus(1);
            // add logging
            $output->clear();
            try {
                $this->runSubRoutine->execute($input, $output);
            } catch (Exception $err) {
                $output->write($err);
            }

            for ($i = 0; $i < 5; $i++) {
                $this->setStatus(1);
                $output->writeln(sprintf('sleeping in stages (%d)', $sleepTimout));
                sleep($sleepTimout);

                $this->checkTerminated();
                if ($this->terminated) {
                    $output->writeln('oops. got to go.');
                    $this->handleSignal(SIGTERM);
                    break;
                }
            }
        }

        $this->setStatus(0);

        $output->writeln(sprintf('received signal to terminate (%d)', $this->terminated));
        $this->terminateSubRoutine->execute($input, $output);

        return 0;
    }

    private function setStatus(int $value) {
        $filename = $this->kernelProjectDir . '/running';
        file_put_contents($filename, $value);
    }

    private function checkTerminated(): void {
        $filename = $this->kernelProjectDir . '/terminate';
        if (file_exists($filename)) {
            $term = file_get_contents($filename);
            if ($term) {
                file_put_contents($filename, '0');
                $this->handleSignal(SIGTERM);
            }
        }
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
