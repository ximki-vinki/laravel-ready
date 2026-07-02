<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Analysis\LegacyFinding;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\Output\LaravelReadyOutput;
use LaravelReady\Console\Output\LegacyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class ReadinessPresenter
{
    public function present(ReadinessResult $readiness, string $relativePath, OutputInterface $output): int
    {
        if ($readiness->actual === ReadinessLevel::LaravelReady && ! $this->hasBlockers($readiness)) {
            (new LaravelReadyOutput)->write($output, $readiness->findings, $relativePath, $readiness->actual);
        } else {
            (new LegacyOutput)->write($output, $readiness->findings, $relativePath, $readiness->actual);
        }

        return $readiness->pledgeViolated === true
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    private function hasBlockers(ReadinessResult $readiness): bool
    {
        return $readiness->findings->contains(
            fn ($finding): bool => $finding instanceof LegacyFinding,
        );
    }
}
