<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Use\LaravelAdapterUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LaravelReadyUsePolicy;

final readonly class UseDependencyChecker
{
    private LaravelReadyUsePolicy $laravelReadyPolicy;

    private LaravelAdapterUsePolicy $laravelAdapterPolicy;

    public function __construct(string $appRoot)
    {
        $this->laravelReadyPolicy = new LaravelReadyUsePolicy($appRoot);
        $this->laravelAdapterPolicy = new LaravelAdapterUsePolicy($appRoot);
    }

    public function check(AnalysisResult $result, ReadinessLevel $actual): AnalysisResult
    {
        $violations = match ($actual) {
            ReadinessLevel::LaravelReady => $this->laravelReadyPolicy->violations($result),
            ReadinessLevel::LaravelAdapter => $this->laravelAdapterPolicy->violations($result),
            default => collect(),
        };

        if ($violations->isEmpty()) {
            return $result;
        }

        return new AnalysisResult(
            findings: $result->findings->merge($violations),
        );
    }
}
