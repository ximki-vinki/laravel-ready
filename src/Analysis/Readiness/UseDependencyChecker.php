<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;

final class UseDependencyChecker
{
    private const string DENIED_NAMESPACE_PREFIX = 'Wf\\';

    private const string PROJECT_NAMESPACE_PREFIX = 'App\\';

    private const array APP_FILE_EXTENSIONS = [
        '.php',       // @pest-mutate-ignore: RemoveArrayItem
        '.class.php', // @pest-mutate-ignore: RemoveArrayItem
    ];

    public function __construct(
        private readonly string $appRoot,
    ) {}

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
        // TODO пока защищаем только LaravelReady
        return TagFinding::uniqueTags($result->findings)->contains(Tag::LaravelReady);
    }

    /**
     * @return Collection<array-key, UseFinding>
     */
    private function violations(AnalysisResult $result): Collection
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

            if ($this->isDeniedAppImport($finding)) {
                $violations->push(new UseFinding($finding->fqcn, $finding->line));
            }
        }

        return $violations;
    }

    private function isDeniedWfImport(UseImportFinding $import): bool
    {
        return str_starts_with($import->fqcn, self::DENIED_NAMESPACE_PREFIX);
    }

    private function isDeniedAppImport(UseImportFinding $import): bool
    {
        if (! str_starts_with($import->fqcn, self::PROJECT_NAMESPACE_PREFIX)) {
            return false;
        }

        $path = $this->resolveAppPath($import->fqcn);

        if ($path === null) {
            return true;
        }

        $tags = TagFinding::uniqueTags((new Detector)->analyse($path)->findings);

        return ! $tags->contains(Tag::LaravelReady)
            && ! $tags->contains(Tag::LaravelAdapter);
    }

    private function resolveAppPath(string $fqcn): ?string
    {
        $relativePath = self::PROJECT_NAMESPACE_PREFIX
                |> strlen(...)
                |> (fn ($x) => substr($fqcn, $x))
                |> (fn ($x) => str_replace('\\', '/', $x));

        foreach (self::APP_FILE_EXTENSIONS as $extension) {
            $path = $this->appRoot.'/'.$relativePath.$extension;

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
