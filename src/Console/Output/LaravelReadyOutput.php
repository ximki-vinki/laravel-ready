<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\ReadinessLevel;
use Symfony\Component\Console\Output\OutputInterface;

final class LaravelReadyOutput
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function write(
        OutputInterface $output,
        Collection $findings,
        string $relativePath,
        ReadinessLevel $level,
    ): void {
        $output->writeln($this->header($relativePath, $level));
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
