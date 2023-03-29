<?php

namespace App\Entity;

use App\Repository\InputRepository;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: InputRepository::class)]
class Input extends AbstractLogEntry
{
}
