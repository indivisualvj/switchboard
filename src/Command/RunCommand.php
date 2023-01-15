<?php

namespace App\Command;

use App\Manager\InputManager;
use App\Manager\OutputManager;
use App\Manager\RuleManager;
use App\Rule\RuleInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    const LINE_LENGTH = 80;

    public function __construct(
        private readonly RuleManager $ruleManager,
        private readonly InputManager $inputManager,
        private readonly OutputManager$outputManager,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run application')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $values = [];
        $inputs = $this->inputManager->getInputs();
        foreach ($inputs as $key => $input) {
            $values[$key] = $input->read($output);
            $output->writeln(sprintf('reading from %s is: %s', $key, $values[$key]));
        }
        $output->writeln(str_repeat('#', self::LINE_LENGTH));
        $rules = $this->ruleManager->getRules();

        /** @var RuleInterface $rule */
        foreach ($rules as $key => $rule) {
            $output->writeln(sprintf('##### %s #####', $key));
            $value = $rule->getInputKey() ? $values[$rule->getInputKey()] : $values;
            if ($rule->execute($value)) {
                $outputKeys = $rule->getTrueOutputs();

            } else {
                $outputKeys = $rule->getFalseOutputs();
            }

            foreach ($outputKeys as $outputKey) {
                $output->writeln(sprintf('executing [%s] ...', $outputKey));
                $output->writeln(sprintf('reason: %s', $rule->reason($value)));
                $output->writeln('feedback:');
                $this->outputManager->getOutput($outputKey)->write($output);
            }

            $output->writeln(str_repeat('_', self::LINE_LENGTH));
        }

        return 0;
    }
}
