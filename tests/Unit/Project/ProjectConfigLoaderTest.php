<?php

declare(strict_types=1);

use LaravelReady\Project\NamespaceResolver;
use LaravelReady\Project\ProjectConfigLoader;

covers(ProjectConfigLoader::class);

it('loads a single resolver from json', function (): void {
    $dir = projectRoot().'/tests/Fixtures/Config/single';

    $config = (new ProjectConfigLoader)->load($dir.'/laravel-ready.json');

    expect($config->resolvers->all())->toEqual([
        new NamespaceResolver('App\\', $dir.'/project/app'),
    ]);
});

it('loads multiple resolvers from json', function (): void {
    $dir = projectRoot().'/tests/Fixtures/Config/multiple';

    $config = (new ProjectConfigLoader)->load($dir.'/laravel-ready.json');

    expect($config->resolvers->all())->toEqual([
        new NamespaceResolver('App\\', $dir.'/project/app'),
        new NamespaceResolver('Domain\\', $dir.'/project/domain'),
    ]);
});

it('rejects an empty prefix', function (): void {
    $path = projectRoot().'/tests/Fixtures/Config/empty-prefix/laravel-ready.json';

    expect(fn () => (new ProjectConfigLoader)->load($path))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects an empty path', function (): void {
    $path = projectRoot().'/tests/Fixtures/Config/empty-path/laravel-ready.json';

    expect(fn () => (new ProjectConfigLoader)->load($path))
        ->toThrow(InvalidArgumentException::class);
});
