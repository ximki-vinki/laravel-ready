<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use LaravelReady\Analysis\Readiness\ReadinessResolver;
use LaravelReady\Analysis\Readiness\ReadinessResult;

final readonly class AnalyseRunner
{
    public function __construct(
        private Detector $detector,
        private ReadinessResolver $readinessResolver,
    ) {}

    public function analyse(string $path): ReadinessResult
    {
        return $this->readinessResolver->resolve(
            $this->detector->analyse($path),
        );
    }
}
