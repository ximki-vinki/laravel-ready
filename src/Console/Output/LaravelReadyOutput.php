<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessLevel;
use Symfony\Component\Console\Output\OutputInterface;

final class LaravelReadyOutput
{
    public function write(OutputInterface $output): void
    {
        $output->writeln('<comment>'.ReadinessLevel::LaravelReady->value.'</comment>');
    }
}
