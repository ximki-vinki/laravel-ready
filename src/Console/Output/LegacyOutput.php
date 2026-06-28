<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\ReadinessLevel;
use Symfony\Component\Console\Output\OutputInterface;

final class LegacyOutput
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function write(OutputInterface $output, Collection $findings): void
    {
        $output->writeln('<fg=red>'.ReadinessLevel::Legacy->value.'</>');
        $output->writeln('  '.$findings
            ->map(fn (Finding $finding): string => $finding->display())
            ->implode(', '));
    }
}
