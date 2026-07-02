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
        $tags = TagStatus::fromFindings($findings);
        $line = $relativePath.' : '.$level->value.' '.$tags->display();

        $output->writeln(match ($level) {
            ReadinessLevel::LaravelReady => '<comment>'.$line.'</>',
            default => $line,
        });
    }
}
