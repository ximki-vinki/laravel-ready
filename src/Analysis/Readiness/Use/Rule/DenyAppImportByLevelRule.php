<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use\Rule;

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessLevelResolver;
use LaravelReady\Analysis\Readiness\Use\UseRule;

final readonly class DenyAppImportByLevelRule implements UseRule
{
    private const string NAMESPACE_PREFIX = 'App\\';

    private const array DEFAULT_FILE_EXTENSIONS = [
        '.php', // @pest-mutate-ignore: RemoveArrayItem
    ];

    /**
     * @param  list<ReadinessLevel>  $allowedDependencyLevels
     * @param  list<string>  $additionalFileExtensions
     */
    public function __construct(
        private string $appRoot,
        private array $allowedDependencyLevels,
        private array $additionalFileExtensions = [],
    ) {}

    public function isDenied(UseImportFinding $import): bool
    {
        if (! str_starts_with($import->fqcn, self::NAMESPACE_PREFIX)) {
            return false;
        }

        $path = $this->resolve($import->fqcn);

        if ($path === null) {
            return true;
        }

        $dependencyLevel = (new ReadinessLevelResolver)->fromResult((new Detector)->analyse($path));

        return ! in_array($dependencyLevel, $this->allowedDependencyLevels, true);
    }

    private function resolve(string $fqcn): ?string
    {
        $relativePath = self::NAMESPACE_PREFIX
                |> strlen(...)
                |> (fn ($x): string => substr($fqcn, $x))
                |> (fn ($x): string => str_replace('\\', '/', $x));

        foreach ($this->fileExtensions() as $extension) {
            $path = $this->appRoot.'/'.$relativePath.$extension;

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function fileExtensions(): array
    {
        return array_merge(self::DEFAULT_FILE_EXTENSIONS, $this->additionalFileExtensions);
    }
}
