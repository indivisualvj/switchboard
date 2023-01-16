<?php

namespace App\Output;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Process\Process;

class CommandOutput implements OutputInterface
{
    public function __construct(
        private readonly array $config,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function write(?\Symfony\Component\Console\Output\OutputInterface $output): int
    {
        return $this->execute($output);
    }


    /**
     * @throws Exception
     */
    private function execute(?\Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $process = new Process($this->getCommand());
        $process->setTimeout(0);
        $process->setTty(false);
        $process->start();

        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $output->writeln($data);
            } else {
                $errorOutput->writeln($data);
            }
        }

        if (!$process->isSuccessful()) {
            throw new Exception(sprintf('Command failed [%s]', implode(' ', $this->getCommand())));
        }

        return 0;
    }

    private function getCommand(): array
    {
        return $this->config['command'] ?? [];
    }

    public function runOnTermSignal(): bool
    {
        return $this->config['run_on_term_signal'] ?? false;
    }

}
