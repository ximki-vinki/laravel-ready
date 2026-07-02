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
    public function write(OutputInterface $output, Collection $findings, string $relativePath): void
    {
        $output->writeln('<fg=red>'.$relativePath.' : '.ReadinessLevel::Legacy->value.'</>');

        foreach ((new FindingSectionBuilder)->build($findings) as $section) {
            $output->writeln('  '.$this->format($section));
        }
    }

    private function format(FindingSection $section): string
    {
        $items = $section->findings
            ->map(fn (Finding $finding): string => $finding->display())
            ->all();

        return $section->label->value.': '.implode(', ', $items);
    }
}
