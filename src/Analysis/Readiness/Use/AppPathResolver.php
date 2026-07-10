<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

final class AppPathResolver
{
    private const string NAMESPACE_PREFIX = 'App\\';

    /**
     * @param  list<string>  $fileExtensions
     */
    public function __construct(
        private readonly string $appRoot,
        private readonly array $fileExtensions,
    ) {}

    public static function matches(string $fqcn): bool
    {
        return str_starts_with($fqcn, self::NAMESPACE_PREFIX);
    }

    public function resolve(string $fqcn): ?string
    {
        $relativePath = self::NAMESPACE_PREFIX
                |> strlen(...)
                |> (fn ($x) => substr($fqcn, $x))
                |> (fn ($x) => str_replace('\\', '/', $x));

        foreach ($this->fileExtensions as $extension) {
            $path = $this->appRoot.'/'.$relativePath.$extension;

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
