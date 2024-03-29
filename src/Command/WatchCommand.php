<?php

namespace App\Command;

use App\SubRoutine\RunSubRoutine;
use App\SubRoutine\TerminateSubRoutine;
use App\Util\StringUtil;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WatchCommand extends Command implements SignalableCommandInterface
{
    private bool $terminated = false;
    private bool $idle = false;
    private bool $restart = false;

    public function __construct(
        private readonly RunSubRoutine $runSubRoutine,
        private readonly TerminateSubRoutine $terminateSubRoutine,
        private readonly string $kernelProjectDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
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

        $this->resetStatus();
        $sleepTimout = $input->getArgument('interval') / 5;

        while (!$this->terminated) {
            $this->setRunning(1);
            $output->clear();

            if (!$this->idle) {
                try {
                    $this->runSubRoutine->execute($input, $output);

                } catch (Exception $err) {
                    $output->write($err);
                }
            } else {
                $output->writeln(StringUtil::lineFill('idle', '+'));
            }

            for ($i = 0; $i < 5; $i++) {
                $this->setRunning(1);
                sleep($sleepTimout);

                $this->checkStop();
                $this->checkRestart();
                $this->checkIdle();

                if ($this->terminated || $this->restart) {
                    break;
                }
            }
        }

        $this->resetStatus();

        if ($this->restart) {
            $command = sprintf(
                'cd %s; bin/console cache:clear; echo "" > var/log/watch.log; bin/console watch %s > var/log/watch.log &',
                $this->kernelProjectDir,
                $input->getArgument('interval')
            );
            $output->writeln(sprintf('received signal to restart. i\'ll be back.'));
            $output->writeln($command);
            $process = Process::fromShellCommandline($command);
            $process->setTty(false);
            $process->setTimeout(360);
            $process->start();
            $process->waitUntil(function ($type, $buffer) use ($output) {
                $output->writeln($buffer);
                return str_contains($buffer, '[OK] Cache');
            });

            sleep(2);

        } else {
            $output->writeln(sprintf('received signal to terminate. good bye.'));
            $this->terminateSubRoutine->execute($input, $output);
        }

        return 0;
    }

    private function resetStatus(): void
    {
        $value = 0;
        $this->setRunning($value);
        $filename = $this->kernelProjectDir . '/idle';
        file_put_contents($filename, $value);
        $filename = $this->kernelProjectDir . '/stop';
        file_put_contents($filename, $value);
        $filename = $this->kernelProjectDir . '/restart';
        file_put_contents($filename, $value);
    }

    private function setRunning(int $value): void
    {
        $filename = $this->kernelProjectDir . '/running';
        file_put_contents($filename, $value);
    }

    private function checkStop(): void
    {
        $filename = $this->kernelProjectDir . '/stop';
        if (file_exists($filename)) {
            $contents = (int) file_get_contents($filename);
            if ($contents) {
                $this->handleSignal(SIGTERM);
            }
        }
    }

    private function checkRestart(): void {
        $this->restart = false;
        $filename = $this->kernelProjectDir . '/restart';
        if (file_exists($filename)) {
            $contents = (int) file_get_contents($filename);
            if ($contents) {
                $this->restart = true;
                $this->handleSignal(SIGTERM);
            }
        }
    }

    private function checkIdle(): void {
        $this->idle = false;
        $filename = $this->kernelProjectDir . '/idle';
        if (file_exists($filename)) {
            $contents = (int)file_get_contents($filename);
            if ($contents) {
                $this->idle = true;
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
