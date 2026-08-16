<?php

declare(strict_types=1);

use LaravelReady\Project\NamespaceResolver;
use LaravelReady\Project\ProjectConfigLoader;

covers(ProjectConfigLoader::class);

it('loads a single resolver from json', function (): void {
    $dir = projectRoot().'/tests/Fixtures/Config/single';

    $result = (new ProjectConfigLoader)->load($dir.'/laravel-ready.json');

    expect($result->isSuccess())->toBeTrue()
        ->and($result->config->resolvers->all())->toEqual([
            new NamespaceResolver('App\\', $dir.'/project/app'),
        ]);
});

it('loads multiple resolvers from json', function (): void {
    $dir = projectRoot().'/tests/Fixtures/Config/multiple';

    $result = (new ProjectConfigLoader)->load($dir.'/laravel-ready.json');

    expect($result->isSuccess())->toBeTrue()
        ->and($result->config->resolvers->all())->toEqual([
            new NamespaceResolver('App\\', $dir.'/project/app'),
            new NamespaceResolver('Domain\\', $dir.'/project/domain'),
        ]);
});

it('rejects an unreadable config', function (): void {
    $path = sys_get_temp_dir().'/laravel-ready-missing-'.uniqid().'.json';

    $result = (new ProjectConfigLoader)->load($path);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->error)->toBe('Project config cannot be read.');
});

it('rejects an empty prefix', function (): void {
    $path = projectRoot().'/tests/Fixtures/Config/empty-prefix/laravel-ready.json';

    $result = (new ProjectConfigLoader)->load($path);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->error)->toBe('Resolver prefix must not be empty.');
});

it('rejects an empty path', function (): void {
    $path = projectRoot().'/tests/Fixtures/Config/empty-path/laravel-ready.json';

    $result = (new ProjectConfigLoader)->load($path);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->error)->toBe('Resolver path must not be empty.');
});

it('rejects invalid json', function (): void {
    $path = projectRoot().'/tests/Fixtures/Config/invalid-json/laravel-ready.json';

    $result = (new ProjectConfigLoader)->load($path);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->error)->toBe('Invalid laravel-ready.json.');
});
