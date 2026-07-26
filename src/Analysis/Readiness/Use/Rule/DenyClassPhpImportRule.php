<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use\Rule;

use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\Use\UseRule;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;

final readonly class DenyClassPhpImportRule implements UseRule
{
    public function __construct(private Psr4ClassLocator $locator) {}

    public function isDenied(UseImportFinding $import): bool
    {
        $path = $this->locator->locate($import->fqcn);

        return $path !== null && str_ends_with($path, '.class.php');
    }
}
