<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final class Psr4ClassResolver
{
    public function __construct(
        private readonly string $projectRoot,
    ) {}

    public function resolve(string $fqcn): ?string
    {
        $composerPath = $this->projectRoot.'/composer.json';

        if (! is_file($composerPath)) {
            return null;
        }

        $contents = file_get_contents($composerPath);

        if ($contents === false) {
            return null;
        }

        $composer = json_decode($contents, true);

        if (! is_array($composer)) {
            return null;
        }

        $autoload = $composer['autoload'] ?? null;

        if (! is_array($autoload)) {
            return null;
        }

        $rawPrefixes = $autoload['psr-4'] ?? null;

        if (! is_array($rawPrefixes)) {
            return null;
        }

        $prefixes = [];

        foreach ($rawPrefixes as $prefix => $directory) {
            if (! is_string($prefix) || ! is_string($directory)) {
                continue;
            }

            $prefixes[$prefix] = $directory;
        }

        foreach ($this->sortedPrefixes($prefixes) as $prefix => $directory) {
            if (! str_starts_with($fqcn, $prefix)) {
                continue;
            }

            $relativeClass = substr($fqcn, strlen($prefix));
            $relativePath = str_replace('\\', '/', $relativeClass).'.php';
            $path = $this->projectRoot.'/'.trim($directory, '/').'/'.$relativePath;

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $prefixes
     * @return array<string, string>
     */
    private function sortedPrefixes(array $prefixes): array
    {
        uksort(
            $prefixes,
            fn (string $left, string $right): int => strlen($right) <=> strlen($left),
        );

        return $prefixes;
    }
}
