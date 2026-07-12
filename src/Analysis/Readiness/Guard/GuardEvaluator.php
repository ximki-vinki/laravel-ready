<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Guard;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\LegacyFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;

final class GuardEvaluator
{
    public function hasBlockers(AnalysisResult $result, ReadinessLevel $actual): bool
    {
        return match ($actual) {
            ReadinessLevel::MultiTag,
            ReadinessLevel::Untagged => true,
            ReadinessLevel::LaravelReady => $this->hasLegacyFinding($result) || $this->hasUseFinding($result),
            ReadinessLevel::LaravelAdapter => $this->hasLegacyFinding($result),
            ReadinessLevel::LegacyAdapter => $this->hasUseFinding($result),
            // ReadinessLevel::Legacy as default
            default => false,
        };
    }

    private function hasLegacyFinding(AnalysisResult $result): bool
    {
        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        );
    }

    private function hasUseFinding(AnalysisResult $result): bool
    {
        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof UseFinding,
        );
    }
}
