<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;
use Symfony\Component\Console\Output\OutputInterface;

final class LaravelReadyOutput
{
    public function write(OutputInterface $output, ReadinessResult $readiness, string $relativePath): void
    {
        $output->writeln($this->header($relativePath, $readiness->actual));
    }

    private function header(string $relativePath, ReadinessLevel $level): string
    {
        $line = $relativePath.' : '.$level->value;

        return match ($level) {
            ReadinessLevel::LaravelReady => '<comment>'.$line.'</>',
            default => $line,
        };
    }
}
