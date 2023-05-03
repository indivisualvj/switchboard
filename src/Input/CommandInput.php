<?php

namespace App\Input;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CommandInput extends AbstractInput
{
    /**
     * @throws Exception
     */
    public function read(?OutputInterface $output)
    {
        return $this->execute($output);
    }


    /**
     * @throws Exception
     */
    private function execute(?OutputInterface $output)
    {
        $command = implode(' ', $this->getCommand());
        $process = Process::fromShellCommandline($command);
        $process->setTty(false);
        $process->start();

        $result = null;
        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $result = $data;
            } else {
                $errorOutput->writeln(trim($data));
            }
        }

        if (!$process->isSuccessful()) {
            throw new Exception(sprintf('Command failed [%s]', implode(' ', $this->getCommand())));
        }

        if ($result) {
            return $result;
        }

        return $this->getDefault();
    }

    private function getCommand(): array
    {
        return $this->config['command'];
    }
}
