<?php

namespace App\Output;

interface OutputInterface
{
    public function write(\Symfony\Component\Console\Output\OutputInterface $output): int;
    public function runOnTermSignal(): bool;
}
