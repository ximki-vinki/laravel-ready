<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Symfony\Component\Finder\SplFileInfo;

final readonly class AnalysableFile
{
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
    ) {}

    public static function fromExplicitFile(string $path): self
    {
        return new self($path, basename($path));
    }

    public static function fromDirectoryEntry(SplFileInfo $file): self
    {
        return new self($file->getPathname(), $file->getRelativePathname());
    }
}
