<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessLevel;
use Symfony\Component\Console\Output\OutputInterface;

final class LegacyOutput
{
    public function write(OutputInterface $output, string $blocker): void
    {
        $output->writeln('<fg=red>'.ReadinessLevel::Legacy->value.'</>');
        $output->writeln('  $'.$blocker);
    }
}
