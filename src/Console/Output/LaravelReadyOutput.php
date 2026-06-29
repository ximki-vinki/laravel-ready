<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessLevel;
use Symfony\Component\Console\Output\OutputInterface;

final class LaravelReadyOutput
{
    public function write(OutputInterface $output, string $relativePath): void
    {
        $output->writeln($relativePath);
        $output->writeln('<comment>'.$relativePath.':'.ReadinessLevel::LaravelReady->value.'</comment>');
    }
}
