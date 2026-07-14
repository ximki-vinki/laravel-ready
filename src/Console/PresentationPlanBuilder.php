<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessResult;
use LaravelReady\Console\Output\ReadinessFooter;

final class PresentationPlanBuilder
{
    public function build(ReadinessResult $readiness): PresentationPlan
    {
        if ($this->isSkipped($readiness)) {
            return new PresentationPlan(
                headerStyle: HeaderStyle::Warning,
                showFindings: true,
                footer: ReadinessFooter::SkipCheck,
                exitCode: 0,
            );
        }

        return match ($readiness->actual) {
            ReadinessLevel::Untagged => new PresentationPlan(
                headerStyle: HeaderStyle::Clean,
                showFindings: true,
                footer: ReadinessFooter::NotGuarded,
                exitCode: 1,
            ),
            ReadinessLevel::MultiTag => new PresentationPlan(
                headerStyle: HeaderStyle::Clean,
                showFindings: true,
                footer: ReadinessFooter::MultiTagFailed,
                exitCode: 1,
            ),
            ReadinessLevel::Legacy => new PresentationPlan(
                headerStyle: HeaderStyle::Clean,
                showFindings: true,
                footer: null,
                exitCode: 0,
            ),
            ReadinessLevel::LegacyAdapter => $readiness->hasBlockers
                ? new PresentationPlan(
                    headerStyle: HeaderStyle::Error,
                    showFindings: true,
                    footer: ReadinessFooter::LegacyAdapterFailed,
                    exitCode: 1,
                )
                : new PresentationPlan(
                    headerStyle: HeaderStyle::Clean,
                    showFindings: false,
                    footer: null,
                    exitCode: 0,
                ),
            ReadinessLevel::LegacyPerfect => $readiness->hasBlockers
                ? new PresentationPlan(
                    headerStyle: HeaderStyle::Error,
                    showFindings: true,
                    footer: ReadinessFooter::LegacyPerfectFailed,
                    exitCode: 1,
                )
                : new PresentationPlan(
                    headerStyle: HeaderStyle::Clean,
                    showFindings: false,
                    footer: null,
                    exitCode: 0,
                ),
            ReadinessLevel::LaravelAdapter => $readiness->hasBlockers
                ? new PresentationPlan(
                    headerStyle: HeaderStyle::Error,
                    showFindings: true,
                    footer: ReadinessFooter::AdapterFailed,
                    exitCode: 1,
                )
                : new PresentationPlan(
                    headerStyle: HeaderStyle::Clean,
                    showFindings: false,
                    footer: null,
                    exitCode: 0,
                ),
            ReadinessLevel::LaravelReady => $readiness->hasBlockers
                ? new PresentationPlan(
                    headerStyle: HeaderStyle::Error,
                    showFindings: true,
                    footer: ReadinessFooter::GuardFailed,
                    exitCode: 1,
                )
                : new PresentationPlan(
                    headerStyle: HeaderStyle::Clean,
                    showFindings: false,
                    footer: null,
                    exitCode: 0,
                ),
        };
    }

    private function isSkipped(ReadinessResult $readiness): bool
    {
        if (! $readiness->skipCheck || ! $readiness->hasBlockers) {
            return false;
        }

        return ! in_array($readiness->actual, [
            ReadinessLevel::Untagged,
            ReadinessLevel::MultiTag,
        ], true);
    }
}
