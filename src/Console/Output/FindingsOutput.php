<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\ReadinessResult;
use Symfony\Component\Console\Output\OutputInterface;

final class FindingsOutput
{
    public function write(OutputInterface $output, ReadinessResult $readiness): void
    {
        foreach ((new FindingSectionBuilder)->build($readiness->findings) as $section) {
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
