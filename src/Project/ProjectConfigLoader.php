<?php

declare(strict_types=1);

namespace LaravelReady\Project;

use JsonException;

final readonly class ProjectConfigLoader
{
    public function load(string $path): ProjectConfigLoadResult
    {
        try {
            /** @var array{resolvers: list<array{prefix: string, path: string}>} $data */
            $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new ProjectConfigLoadResult(null, 'Invalid laravel-ready.json.');
        }

        $configDir = dirname($path);
        $resolvers = collect();

        foreach ($data['resolvers'] as $resolver) {
            if ($resolver['prefix'] === '') {
                return new ProjectConfigLoadResult(null, 'Resolver prefix must not be empty.');
            }

            if ($resolver['path'] === '') {
                return new ProjectConfigLoadResult(null, 'Resolver path must not be empty.');
            }

            $resolvers->push(new NamespaceResolver(
                $resolver['prefix'],
                $configDir.'/'.trim($resolver['path'], '/'),
            ));
        }

        return new ProjectConfigLoadResult(new ProjectConfig($resolvers), null);
    }
}
