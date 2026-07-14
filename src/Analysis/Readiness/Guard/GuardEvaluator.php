<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Guard;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\FunctionCallFinding;
use LaravelReady\Analysis\Findings\LegacyFinding;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;

final class GuardEvaluator
{
    public function hasBlockers(AnalysisResult $result, ReadinessLevel $actual): bool
    {
        return match ($actual) {
            ReadinessLevel::MultiTag,
            ReadinessLevel::Untagged => true,
            ReadinessLevel::LaravelReady, ReadinessLevel::LegacyPerfect => $this->hasLegacyFinding($result) || $this->hasUseFinding($result),
            ReadinessLevel::LaravelAdapter => $this->hasLegacyFinding($result),
            ReadinessLevel::LegacyAdapter => $this->hasUseFinding($result) || $this->hasUnpermittedLegacyFinding($result),
            // ReadinessLevel::Legacy as default
            default => false,
        };
    }

    private function hasUnpermittedLegacyFinding(AnalysisResult $result): bool
    {
        $allows = $result->allows ?? collect();

        return $result->findings->contains(function (Finding $finding) use ($allows): bool {
            if (! $finding instanceof LegacyFinding) {
                return false;
            }

            $token = match (true) {
                $finding instanceof SuperglobalFinding => $finding->name,
                $finding instanceof FunctionCallFinding => $finding->function,
                default => null,
            };

            return $token === null || ! $allows->contains($token);
        });
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
