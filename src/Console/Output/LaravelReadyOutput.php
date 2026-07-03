<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessResult;
use Symfony\Component\Console\Output\OutputInterface;

final class LaravelReadyOutput
{
    public function write(OutputInterface $output, ReadinessResult $readiness, string $relativePath): void
    {
        $output->writeln(ReadinessHeader::format($relativePath, $readiness));
    }
}
