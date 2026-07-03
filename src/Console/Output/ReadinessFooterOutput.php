<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;

final class ReadinessFooterOutput
{
    public function write(OutputInterface $output, ReadinessFooter $footer): void
    {
        $output->writeln($footer->value);
    }
}
