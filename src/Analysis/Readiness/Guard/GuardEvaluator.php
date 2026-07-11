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
            ReadinessLevel::LaravelReady => $this->hasAnyLegacyFinding($result),
            ReadinessLevel::LaravelAdapter => $this->hasAstBlocker($result),
            default => false,
        };
    }

    private function hasAnyLegacyFinding(AnalysisResult $result): bool
    {
        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        );
    }

    private function hasAstBlocker(AnalysisResult $result): bool
    {
        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding && ! $finding instanceof UseFinding,
        );
    }
}
