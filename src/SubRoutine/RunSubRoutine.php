<?php

namespace App\SubRoutine;

use App\Factory\RuleFactory;
use App\Manager\InputManager;
use App\Manager\NormalizerManager;
use App\Manager\OutputManager;
use App\Manager\StatisticsManager;
use App\Rule\RuleInterface;
use App\Util\StringUtil;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSubRoutine implements SubRoutineInterface
{
    public function __construct(
        private readonly RuleFactory       $ruleFactory,
        private readonly InputManager      $inputManager,
        private readonly OutputManager     $outputManager,
        private readonly NormalizerManager $normalizerManager,
        private readonly StatisticsManager $statisticsManager,
        private readonly array             $rules,
    ) {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(str_repeat('µ', StringUtil::LINE_LENGTH));
        $output->writeln(StringUtil::lineFill((new \DateTime())->format('Y-m-d H:i:s'), '@'));
        $output->writeln(str_repeat('_', StringUtil::LINE_LENGTH));

        $inputs = $this->readAll($output);
        $rules = $this->ruleFactory->createAll($this->rules);
        $ruleResults = [];
        $outputsSwitched = [];

        $output->writeln(StringUtil::lineFill('rules', '|'));
        /** @var RuleInterface $rule */
        foreach ($rules as $key => $rule) {
            $value = $rule->getInputKey() ? $inputs[$rule->getInputKey()] : $inputs;

            $ruleResults[$key] = $rule->getLastStatus();

            $outputKeys = $rule->execute($value, $output);
            $output->writeln(sprintf('%s: %s', $key, $rule->result($value)));

            foreach ($outputKeys as $outputKey) {
                $outputsSwitched[$outputKey] = true;
                $output->writeln(sprintf('executing: "%s"', $outputKey));
                $output->write('feedback: ');
                $this->outputManager->getOutput($outputKey)->write($output);
            }
            $output->writeln(str_repeat('-', StringUtil::LINE_LENGTH));
        }

        $this->statisticsManager->logInputs($inputs);
        $this->statisticsManager->logRules($ruleResults);
        $this->statisticsManager->logOutputs($outputsSwitched);
        $this->statisticsManager->flush();

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
            $value = $input->getDefault();
            try {
                $value = $input->read($output);
            } catch (Exception $exception) {
                $output->writeln($exception->getMessage());
            }

            $values[$key] = $this->normalizerManager->normalize($config['normalizers'] ?? [], $value, $values, $output);
            $output->writeln(sprintf('input "%s" is: %s', $key, $values[$key]));

        }
        $output->writeln(str_repeat('_', StringUtil::LINE_LENGTH));

        return $values;
    }
}
