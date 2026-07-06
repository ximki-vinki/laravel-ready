<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;

final class UseDependencyChecker
{
    private const string DENIED_NAMESPACE_PREFIX = 'Wf\\';

    private const string PROJECT_NAMESPACE_PREFIX = 'App\\';

    public function __construct(
        private readonly ?string $projectRoot = null,
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
        if ($this->projectRoot === null) {
            return false;
        }

        // TODO временная проверка что мы работаем только с папкой app
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

        foreach ($this->appBaseDirectories() as $directory) {
            $base = $this->projectRoot.'/'.$directory;

            foreach ($this->appFileExtensions() as $extension) {
                $path = $base.'/'.$relativePath.$extension;

                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    private function appBaseDirectories(): array
    {
        return [
            'project/app',
            // TODO для тестов
            'src',
        ];
    }

    /** @return list<string> */
    private function appFileExtensions(): array
    {
        return [
            '.php',
            '.class.php',
        ];
    }
}
