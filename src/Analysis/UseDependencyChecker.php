<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;

final class UseDependencyChecker
{
    private const string DENIED_NAMESPACE_PREFIX = 'Wf\\';

    public function check(AnalysisResult $result): AnalysisResult
    {
        if (! $this->isGuarded($result)) {
            return $result;
        }

        $violations = $this->violations($result);

        if ($violations->isEmpty()) {
            return $result;
        }

        return new AnalysisResult(
            findings: $result->findings->merge($violations),
        );
    }

    private function isGuarded(AnalysisResult $result): bool
    {
        return TagFinding::uniqueTags($result->findings)->contains(Tag::LaravelReady);
    }

    /**
     * @return Collection<array-key, UseFinding>
     */
    private function violations(AnalysisResult $result): Collection
    {
        /** @var Collection<array-key, UseImportFinding> $imports */
        $imports = $result->findings->filter(
            fn (Finding $finding): bool => $finding instanceof UseImportFinding
                && str_starts_with($finding->fqcn, self::DENIED_NAMESPACE_PREFIX),
        );

        return $imports
            ->map(fn (UseImportFinding $import): UseFinding => new UseFinding(
                $import->fqcn,
                $import->line,
            ));
    }
}
