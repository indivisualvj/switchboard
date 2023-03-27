<?php

namespace App\SubRoutine;

use App\Factory\RuleFactory;
use App\Manager\InputManager;
use App\Manager\NormalizerManager;
use App\Manager\OutputManager;
use App\Rule\RuleInterface;
use App\Util\StringUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSubRoutine implements SubRoutineInterface
{
    public function __construct(
        private readonly RuleFactory       $ruleFactory,
        private readonly InputManager      $inputManager,
        private readonly OutputManager     $outputManager,
        private readonly NormalizerManager $normalizerManager,
        private readonly array             $rules,
    ) {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(str_repeat('µ', StringUtil::LINE_LENGTH));
        $output->writeln(StringUtil::lineFill((new \DateTime())->format('Y-m-d H:i:s'), '@'));
        $output->writeln(str_repeat('_', StringUtil::LINE_LENGTH));

        $values = $this->readAll($output);
        $rules = $this->ruleFactory->createAll($this->rules);

        $output->writeln(StringUtil::lineFill('rules', '|'));
        /** @var RuleInterface $rule */
        foreach ($rules as $key => $rule) {
            $output->writeln(sprintf('rule %s', $key));
            $value = $rule->getInputKey() ? $values[$rule->getInputKey()] : $values;
            $outputKeys = $rule->execute($value, $output);

            $output->writeln(sprintf('result: %s', $rule->result($value)));
            foreach ($outputKeys as $outputKey) {
                $output->writeln(sprintf('therefore executing [%s] ...', $outputKey));
                $output->writeln('feedback:');
                $this->outputManager->getOutput($outputKey)->write($output);
            }
            $output->writeln(str_repeat('_', StringUtil::LINE_LENGTH));
        }

        return 0;
    }

    private function readAll(OutputInterface $output): array
    {
        $values = [];
        $inputs = $this->inputManager->getInputs();

        $output->writeln(StringUtil::lineFill('inputs', '|'));

        /** @var  $input \App\Input\InputInterface */
        foreach ($inputs as $key => $input) {
            $config = $input->getConfig();
            $values[$key] = $this->normalizerManager->normalize($config['normalizers'] ?? [], $input->read($output), $values);
            $output->writeln(sprintf('input "%s" is: %s', $key, $values[$key]));
        }
        $output->writeln(str_repeat('_', StringUtil::LINE_LENGTH));

        return $values;
    }
}
