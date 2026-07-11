<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use\Rule;

use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\Use\UseRule;

final readonly class DenyWfNamespaceRule implements UseRule
{
    private const string DENIED_NAMESPACE_PREFIX = 'Wf\\';

    public function isDenied(UseImportFinding $import): bool
    {
        return str_starts_with($import->fqcn, self::DENIED_NAMESPACE_PREFIX);
    }
}
