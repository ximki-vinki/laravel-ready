<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Resolution;

use Illuminate\Support\Collection;
use LaravelReady\Project\NamespaceResolver;

final readonly class Psr4ClassLocator
{
    /**
     * @param  Collection<int, NamespaceResolver>  $resolvers
     */
    public function __construct(private Collection $resolvers) {}

    public function locate(string $fqcn): ?string
    {
        foreach ($this->resolvers as $resolver) {
            if (! str_starts_with($fqcn, $resolver->prefix)) {
                continue;
            }

            $relative = $resolver->prefix
                    |> strlen(...)
                    |> (fn ($x) => substr($fqcn, $x))
                    |> (fn ($x) => str_replace('\\', '/', $x));
            $path = $resolver->path.'/'.$relative.'.php';

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
