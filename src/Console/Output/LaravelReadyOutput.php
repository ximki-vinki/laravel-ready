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
    public function write(OutputInterface $output, Collection $findings, string $relativePath): void
    {
        $tags = TagStatus::fromFindings($findings);

        $output->writeln('<comment>'.$relativePath.' : '.ReadinessLevel::LaravelReady->value.' '.$tags->display().'</comment>');
    }
}
