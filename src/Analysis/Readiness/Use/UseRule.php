<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Findings\UseImportFinding;

interface UseRule
{
    public function isDenied(UseImportFinding $import): bool;
}
