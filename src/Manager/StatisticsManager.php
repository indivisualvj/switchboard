<?php

namespace App\Manager;

use App\Entity\AbstractLogEntry;
use App\Entity\Input;
use App\Entity\Output;
use App\Entity\Rule;
use App\Repository\InputRepository;
use App\Repository\OutputRepository;
use App\Repository\RuleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsManager
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OutputManager $outputManager,
    )
    {
    }

    public function logInputs(array $inputs): void
    {
        $loggingTime = $this->isLoggingTime(new DateTime());

        foreach ($inputs as $key => $value) {
            if (is_numeric($value) || is_bool($value)) {
                $entry = new Input();
                $entry->setName($key);
                $entry->setValue($value);

                if ($loggingTime) {
                    $history = $this->getInputRepository()->findLastMinutes($key, 5);
                    $this->updateAverage($entry, $history);
                }

                $this->entityManager->persist($entry);
            }
        }
    }

    public function logRules(array $rules): void
    {
        $loggingTime = $this->isLoggingTime(new DateTime());

        foreach ($rules as $key => $value) {
            $entry = new Rule();
            $entry->setName($key);
            $entry->setValue($value);

            if ($loggingTime) {
                $history = $this->getRuleRepository()->findLastMinutes($key, 5);
                $this->updateAverage($entry, $history);
            }

            $this->entityManager->persist($entry);
        }

    }

    public function logOutputs(array $outputs): void
    {
        $loggingTime = $this->isLoggingTime(new DateTime());
        foreach ($this->outputManager->getOutputs() as $key => $value) {
            $entry = new Output();
            $entry->setName($key);
            $entry->setValue(isset($outputs[$key]));

            if ($loggingTime) {
                $history = $this->getOutputRepository()->findLastMinutes($key, 10);
                $this->updateAverage($entry, $history);
            }

            $this->entityManager->persist($entry);
        }
    }

    public function isLoggingTime(DateTime $dateTime): bool
    {
        $mod = $dateTime->format('i') % 2;
        return 0 === $mod;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function getInputStatistics($minutes): array
    {
        $history = $this->getInputRepository()->findStatistics($minutes);
        return $this->getAverages($history);
    }

    public function getRuleStatistics($minutes): array
    {
        $history = $this->getRuleRepository()->findStatistics($minutes);
        return $this->getAverages($history);
    }

    public function getOutputStatistics($minutes): array
    {
        $history = $this->getOutputRepository()->findStatistics($minutes);
        return $this->getAverages($history);
    }

    private function getAverages($history): array
    {
        $grouped = [];
        /** @var AbstractLogEntry $stat */
        foreach ($history as $stat) {
            $key = $stat->getName();
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $stat;
        }

        $averages = [];
        foreach ($grouped as $key => $values) {
            $sum = 0;
            /** @var AbstractLogEntry $value */
            foreach ($values as $value) {
                $sum += $value->getValue();
            }
            $avg = $sum / count($values);

            $averages[$key] = round($avg, 2);
        }

        return $averages;
    }

    private function updateAverage(AbstractLogEntry $entry, $history): void
    {
        $sum = 0;
        /** @var AbstractLogEntry $item */
        foreach ($history as $item) {
            if ($item->isAveraged()) {
                return;
            }
            $sum += (int)$item->getValue();
        }
        /** @var AbstractLogEntry $item */
        foreach ($history as $item) {
            $this->entityManager->remove($item);
        }

        $avg = $sum / count($history);
        if (abs($avg) <= 1) {
            $avg *= 100;
        }

        $entry->setAveraged(true);
        $entry->setValue((int)$avg);
    }

    private function getInputRepository(): InputRepository
    {
        return $this->entityManager->getRepository(Input::class);
    }

    private function getRuleRepository(): RuleRepository
    {
        return $this->entityManager->getRepository(Rule::class);
    }

    private function getOutputRepository(): OutputRepository
    {
        return $this->entityManager->getRepository(Output::class);
    }
}
