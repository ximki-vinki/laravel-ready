<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Use\LaravelAdapterUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LaravelReadyUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LegacyAdapterUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LegacyPerfectUsePolicy;

final readonly class UseDependencyChecker
{
    public function __construct(private string $appRoot) {}

    public function check(AnalysisResult $result, ReadinessLevel $actual): AnalysisResult
    {
        $policy = match ($actual) {
            ReadinessLevel::LaravelReady => new LaravelReadyUsePolicy($this->appRoot),
            ReadinessLevel::LaravelAdapter => new LaravelAdapterUsePolicy($this->appRoot),
            ReadinessLevel::LegacyAdapter => new LegacyAdapterUsePolicy($this->appRoot),
            ReadinessLevel::LegacyPerfect => new LegacyPerfectUsePolicy($this->appRoot),
            // ReadinessLevel::Legacy as default
            default => null,
        };

        if ($policy === null) {
            return $result;
        }

        $violations = $policy->violations($result);

        if ($violations->isEmpty()) {
            return $result;
        }

        return new AnalysisResult(
            findings: $result->findings->merge($violations),
            skipCheck: $result->skipCheck,
            allows: $result->allows,
        );
    }
}
