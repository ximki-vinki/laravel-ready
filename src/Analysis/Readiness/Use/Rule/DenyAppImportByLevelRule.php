<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use\Rule;

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessLevelResolver;
use LaravelReady\Analysis\Readiness\Use\UseRule;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;

final readonly class DenyAppImportByLevelRule implements UseRule
{
    /**
     * @param  list<ReadinessLevel>  $allowedDependencyLevels
     */
    public function __construct(
        private Psr4ClassLocator $locator,
        private array $allowedDependencyLevels,
    ) {}

    public function isDenied(UseImportFinding $import): bool
    {
        $path = $this->locator->locate($import->fqcn);

        if ($path === null) {
            return false;
        }

        $dependencyLevel = (new ReadinessLevelResolver)->fromResult((new Detector)->analyse($path));

        return ! in_array($dependencyLevel, $this->allowedDependencyLevels, true);
    }
}
