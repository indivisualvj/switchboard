<?php

namespace App\SubRoutine;

use App\Factory\RuleFactory;
use App\Manager\InputManager;
use App\Manager\NormalizerManager;
use App\Manager\OutputManager;
use App\Rule\RuleInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSubRoutine implements SubRoutineInterface
{
    private $values = [];

    public function __construct(
        private readonly RuleFactory       $ruleFactory,
        private readonly InputManager      $inputManager,
        private readonly OutputManager     $outputManager,
        private readonly NormalizerManager $normalizerManager,
        private readonly array             $rules,
    ) {
    }

    const LINE_LENGTH = 80;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->values = array_merge($this->getOutputValues(), $this->values, $this->readAll($output));
        $rules = $this->ruleFactory->createAll($this->rules);

        /** @var RuleInterface $rule */
        foreach ($rules as $key => $rule) {
            $output->writeln(sprintf('##### %s #####', $key));
            $value = $rule->getInputKey() ? $this->values[$rule->getInputKey()] : $this->values;
            $outputKeys = $rule->execute($value, $output);

            $output->writeln(sprintf('result: %s', $rule->result($value)));
            foreach ($outputKeys as $outputKey) {
                $this->values['_output_' .$outputKey] = true;
                $output->writeln(sprintf('therefore executing [%s] ...', $outputKey));
                $output->writeln('feedback:');
                $this->outputManager->getOutput($outputKey)->write($output);
            }
            $output->writeln(str_repeat('_', self::LINE_LENGTH));
        }

        return 0;
    }

    private function readAll(OutputInterface $output): array
    {
        $values = [];
        $inputs = $this->inputManager->getInputs();

        /** @var  $input \App\Input\InputInterface */
        foreach ($inputs as $key => $input) {
            $config = $input->getConfig();
            $values[$key] = $this->normalizerManager->normalize($config['normalizers'] ?? [], $input->read($output), $values);
            $output->writeln(sprintf('reading from %s is: %s', $key, $values[$key]));
        }
        $output->writeln(str_repeat('#', self::LINE_LENGTH));

        return $values;
    }

    private function getOutputValues(): array
    {
        $result = [];
        $keys = array_keys($this->outputManager->getOutputs());
        foreach ($keys as $key) {
            $result['_output_' .$key] = false;
        }

        return $result;
    }
}
