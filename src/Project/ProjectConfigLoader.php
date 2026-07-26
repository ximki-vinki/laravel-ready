<?php

declare(strict_types=1);

namespace LaravelReady\Project;

use InvalidArgumentException;

final readonly class ProjectConfigLoader
{
    public function load(string $path): ProjectConfig
    {
        /** @var array{resolvers: list<array{prefix: string, path: string}>} $data */
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
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
