<?php

namespace App\Entity;

use App\Repository\OutputRepository;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: OutputRepository::class)]
class Output extends AbstractLogEntry
{
}
