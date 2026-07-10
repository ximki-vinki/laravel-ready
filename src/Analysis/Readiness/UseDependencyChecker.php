<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Use\LaravelReadyUsePolicy;

final readonly class UseDependencyChecker
{
    private LaravelReadyUsePolicy $laravelReadyPolicy;

    public function __construct(string $appRoot)
    {
        $this->laravelReadyPolicy = new LaravelReadyUsePolicy($appRoot);
    }

    public function check(AnalysisResult $result, ReadinessLevel $actual): AnalysisResult
    {
        if ($actual !== ReadinessLevel::LaravelReady) {
            return $result;
        }

        $violations = $this->laravelReadyPolicy->violations($result);

        if ($violations->isEmpty()) {
            return $result;
        }

        return new AnalysisResult(
            findings: $result->findings->merge($violations),
        );
    }
}
