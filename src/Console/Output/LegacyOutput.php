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
    public function write(
        OutputInterface $output,
        Collection $findings,
        string $relativePath,
        ReadinessLevel $level,
    ): void {
        $output->writeln($this->header($relativePath, $level));

        foreach ((new FindingSectionBuilder)->build($findings) as $section) {
            $output->writeln('  '.$this->format($section));
        }
    }

    private function header(string $relativePath, ReadinessLevel $level): string
    {
        $line = $relativePath.' : '.$level->value;

        return match ($level) {
            ReadinessLevel::Legacy => '<fg=red>'.$line.'</>',
            ReadinessLevel::LaravelReady => '<comment>'.$line.'</>',
            ReadinessLevel::MultiTag => '<fg=yellow>'.$line.'</>',
            default => $line,
        };
    }

    private function format(FindingSection $section): string
    {
        $items = $section->findings
            ->map(fn (Finding $finding): string => $finding->display())
            ->all();

        return $section->label->value.': '.implode(', ', $items);
    }
}
