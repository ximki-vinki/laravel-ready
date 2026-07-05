<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;

final class ReportScenarioResolver
{
    public function resolve(ReadinessResult $readiness): ReportScenario
    {
        return match ($readiness->actual) {
            ReadinessLevel::Untagged, ReadinessLevel::MultiTag => ReportScenario::TagInvalid,
            ReadinessLevel::Legacy => ReportScenario::LegacyInfo,
            default => $readiness->hasBlockers ? ReportScenario::GuardFailed : ReportScenario::Clean,
        };
    }
}
