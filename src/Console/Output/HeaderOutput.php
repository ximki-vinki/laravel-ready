<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\HeaderStyle;
use Symfony\Component\Console\Output\OutputInterface;

final class HeaderOutput
{
    public function write(OutputInterface $output, ReadinessResult $readiness, string $relativePath, HeaderStyle $headerStyle): void
    {
        $output->writeln(ReadinessHeader::format($relativePath, $readiness, $headerStyle));
    }
}
