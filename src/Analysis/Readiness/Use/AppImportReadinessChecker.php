<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessLevelResolver;

final readonly class AppImportReadinessChecker
{
    /**
     * @param  list<ReadinessLevel>  $allowedDependencyLevels
     */
    public function __construct(
        private AppPathResolver $appPathResolver,
        private array $allowedDependencyLevels,
    ) {}

    public function isDenied(UseImportFinding $import): bool
    {
        if (! AppPathResolver::matches($import->fqcn)) {
            return false;
        }

        $path = $this->appPathResolver->resolve($import->fqcn);

        if ($path === null) {
            return true;
        }

        $dependencyLevel = (new ReadinessLevelResolver)->fromResult((new Detector)->analyse($path));

        return ! in_array($dependencyLevel, $this->allowedDependencyLevels, true);
    }
}
