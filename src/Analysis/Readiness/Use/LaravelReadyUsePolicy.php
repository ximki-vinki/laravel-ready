<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;

final readonly class LaravelReadyUsePolicy
{
    private const string DENIED_NAMESPACE_PREFIX = 'Wf\\';

    private const array ALLOWED_DEPENDENCY_LEVELS = [
        ReadinessLevel::LaravelReady,   // @pest-mutate-ignore: RemoveArrayItem
        ReadinessLevel::LaravelAdapter, // @pest-mutate-ignore: RemoveArrayItem
    ];

    private const array APP_FILE_EXTENSIONS = [
        '.php',       // @pest-mutate-ignore: RemoveArrayItem
    ];

    private AppImportReadinessChecker $appImportChecker;

    public function __construct(string $appRoot)
    {
        $this->appImportChecker = new AppImportReadinessChecker(
            new AppPathResolver($appRoot, self::APP_FILE_EXTENSIONS),
            self::ALLOWED_DEPENDENCY_LEVELS,
        );
    }

    /**
     * @return Collection<array-key, UseFinding>
     */
    public function violations(AnalysisResult $result): Collection
    {
        $violations = collect();

        foreach ($result->findings as $finding) {
            if (! $finding instanceof UseImportFinding) {
                continue;
            }

            if ($this->isDeniedWfImport($finding)) {
                $violations->push(new UseFinding($finding->fqcn, $finding->line));

                continue;
            }

            if ($this->appImportChecker->isDenied($finding)) {
                $violations->push(new UseFinding($finding->fqcn, $finding->line));
            }
        }

        return $violations;
    }

    private function isDeniedWfImport(UseImportFinding $import): bool
    {
        return str_starts_with($import->fqcn, self::DENIED_NAMESPACE_PREFIX);
    }
}
