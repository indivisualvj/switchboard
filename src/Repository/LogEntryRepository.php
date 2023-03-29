<?php

namespace App\Repository;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityRepository;

class LogEntryRepository extends EntityRepository
{
    public function findLastMinutes(string $name, int $minutes)
    {
        $date = new DateTime();
        $date->sub(DateInterval::createFromDateString(sprintf('%d minute', $minutes)));

        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->andWhere('a.date > :date')
            ->orderBy('a.date', 'DESC')
            ->setParameter('name', $name)
            ->setParameter('date', $date)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findStatistics($minutes)
    {
        $date = new DateTime();
        $date->sub(DateInterval::createFromDateString(sprintf('%d minute', $minutes)));

        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.date > :date')
            ->andWhere('a.averaged = true')
            ->orderBy('a.name', 'ASC')
            ->setParameter('date', $date)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
