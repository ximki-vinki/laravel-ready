<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\SuperglobalFinding;
use Symfony\Component\Console\Output\OutputInterface;

final class LegacyOutput
{
    /** @param  Collection<array-key, SuperglobalFinding>  $findings */
    public function write(OutputInterface $output, Collection $findings): void
    {
        $output->writeln('<fg=red>'.ReadinessLevel::Legacy->value.'</>');
        $output->writeln('  '.$findings
            ->map(fn (SuperglobalFinding $finding): string => $finding->display())
            ->implode(', '));
    }
}
