<?php

namespace App\Entity;

use App\Repository\RuleRepository;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: RuleRepository::class)]
class Rule extends AbstractLogEntry
{
}
