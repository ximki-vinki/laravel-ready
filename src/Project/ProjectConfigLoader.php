<?php

declare(strict_types=1);

namespace LaravelReady\Project;

use InvalidArgumentException;
use RuntimeException;

final readonly class ProjectConfigLoader
{
    public function load(string $path): ProjectConfig
    {
        if (! is_readable($path)) {
            throw new RuntimeException(sprintf('Project config cannot be read: %s', $path));
        }

        $json = file_get_contents($path);

        if ($json === false) { // @pest-mutate-ignore: FalseToTrue
            throw new RuntimeException(sprintf('Project config cannot be read: %s', $path));
        }

        /** @var array{resolvers: list<array{prefix: string, path: string}>} $data */
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $configDir = dirname($path);

        $resolvers = collect($data['resolvers'])
            ->map(function (array $resolver) use ($configDir): NamespaceResolver {
                if ($resolver['prefix'] === '') {
                    throw new InvalidArgumentException('Resolver prefix must not be empty.');
                }

                if ($resolver['path'] === '') {
                    throw new InvalidArgumentException('Resolver path must not be empty.');
                }

                return new NamespaceResolver(
                    $resolver['prefix'],
                    $configDir.'/'.trim($resolver['path'], '/'),
                );
            });

        return new ProjectConfig($resolvers);
    }
}
