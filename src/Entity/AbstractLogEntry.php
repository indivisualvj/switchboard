<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use DateTime;
use Doctrine\ORM\Mapping\PrePersist;

#[MappedSuperclass]
#[HasLifecycleCallbacks]
abstract class AbstractLogEntry
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;
    #[Column]
    private string $name;
    #[Column]
    private ?int $value = null;
    #[Column]
    private DateTime $date;
    #[Column]
    private bool $averaged = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return bool
     */
    public function isAveraged(): bool
    {
        return $this->averaged;
    }

    /**
     * @param bool $averaged
     */
    public function setAveraged(bool $averaged): void
    {
        $this->averaged = $averaged;
    }

    /**
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int|null $value
     */
    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    #[PrePersist]
    public function onPersist(): void {
        $this->setDate(new DateTime());
    }
}
