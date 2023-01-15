<?php

namespace App\Input;

use App\Normalizer\NormalizerInterface;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CommandInput implements InputInterface
{
    public function __construct(
        private readonly array $command,
        private readonly array $normalizers,
        private readonly array $config,
    )
    {
    }

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
        $process = new Process($this->command);
        $process->setTimeout(0);
        $process->setTty(false);
        $process->start();

        $result = null;
        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $result = $data;
            } else {
                $errorOutput->writeln($data);
            }
        }

        if (!$process->isSuccessful()) {
            throw new Exception(sprintf('Command failed [%s]', implode(' ', $this->command)));
        }

        if ($result) {
            /** @var NormalizerInterface $normalizer */
            foreach ($this->normalizers as $normalizer) {
                $result = $normalizer->normalize($result);
            }

            return $result;
        }

        return $this->config['default'];
    }

}
