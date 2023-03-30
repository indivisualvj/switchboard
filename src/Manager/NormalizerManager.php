<?php

namespace App\Manager;

use App\Factory\NormalizerFactory;
use App\Normalizer\NormalizerInterface;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class NormalizerManager
{
    public function __construct(
        private readonly NormalizerFactory $normalizerFactory,
    ) {}

    public function normalize(array $config, $value, array $values, OutputInterface $output)
    {
        $normalizers = $this->normalizerFactory->createAll($config);

        /** @var NormalizerInterface $normalizer */
        foreach ($normalizers as $normalizer) {
            try {
                $value = $normalizer->normalize($value, $values);
            } catch (Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        }

        return trim($value);
    }
}
