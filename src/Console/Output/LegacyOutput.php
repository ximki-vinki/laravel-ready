<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\SuperglobalFinding;
use Symfony\Component\Console\Output\OutputInterface;

final class LegacyOutput
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function write(OutputInterface $output, Collection $findings): void
    {
        $output->writeln('<fg=red>'.ReadinessLevel::Legacy->value.'</>');

        foreach ($this->groupedLines($findings) as $line) {
            $output->writeln('  '.$line);
        }
    }

    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return list<string>
     */
    private function groupedLines(Collection $findings): array
    {
        $lines = [];

        $superglobals = $findings
            ->filter(fn (Finding $finding): bool => $finding instanceof SuperglobalFinding)
            ->map(fn (Finding $finding): string => $finding->display())
            ->all();

        if ($superglobals !== []) {
            $lines[] = 'var: '.implode(', ', $superglobals);
        }

        $functions = $findings
            ->filter(fn (Finding $finding): bool => $finding instanceof FunctionCallFinding)
            ->map(fn (Finding $finding): string => $finding->display())
            ->all();

        if ($functions !== []) {
            $lines[] = 'func: '.implode(', ', $functions);
        }

        return $lines;
    }
}
