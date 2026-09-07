<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Displayable;
use LaravelReady\Analysis\Readiness\ReadinessResult;
use LaravelReady\Analysis\SkipCheck\SkipCheckEvaluator;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class FindingsOutput
{
    public function __construct(private SkipCheckEvaluator $skipCheck = new SkipCheckEvaluator) {}

    public function write(OutputInterface $output, ReadinessResult $readiness): void
    {
        foreach ((new FindingSectionBuilder)->build(
            $readiness->findings,
            $readiness->allows,
            SkipCheckNote::fromVerdict($this->skipCheck->verdict($readiness->skipCheck)),
        ) as $section) {
            $output->writeln('  '.$this->format($section));
        }
    }

    private function format(FindingSection $section): string
    {
        $items = $section->findings
            ->map(fn (Displayable $item): string => $item->display())
            ->all();

        return $section->label->value.': '.implode(', ', $items);
    }
}
