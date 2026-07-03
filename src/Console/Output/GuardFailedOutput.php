<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;

final class GuardFailedOutput
{
    public function write(OutputInterface $output): void
    {
        $output->writeln('Guard failed: @laravel-ready file must stay LaravelReady.');
    }
}
