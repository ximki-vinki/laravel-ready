<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\Output\LaravelReadyOutput;
use LaravelReady\Console\Output\LegacyOutput;
use LaravelReady\Console\Output\ReadinessFooter;
use LaravelReady\Console\Output\ReadinessFooterOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class ReadinessPresenter
{
    public function present(ReadinessResult $readiness, string $relativePath, OutputInterface $output): int
    {
        if ($readiness->actual === ReadinessLevel::LaravelReady && ! $readiness->hasBlockers) {
            (new LaravelReadyOutput)->write($output, $readiness, $relativePath);
        } else {
            (new LegacyOutput)->write($output, $readiness, $relativePath);

            $footer = match (true) {
                $readiness->actual === ReadinessLevel::LaravelReady && $readiness->hasBlockers => ReadinessFooter::GuardFailed,
                $readiness->actual === ReadinessLevel::MultiTag => ReadinessFooter::MultiTagFailed,
                $readiness->actual === ReadinessLevel::Untagged => ReadinessFooter::NotGuarded,
                default => null,
            };

            if ($footer !== null) {
                (new ReadinessFooterOutput)->write($output, $footer);
            }
        }

        return $readiness->hasBlockers
            ? Command::FAILURE
            : Command::SUCCESS;
    }
}
