<?php

declare(strict_types=1);

use LaravelReady\Project\NamespaceResolver;
use LaravelReady\Project\ProjectConfig;
use LaravelReady\Project\ProjectConfigLoadResult;

covers(ProjectConfigLoadResult::class);

it('reports success when config is present and error is null', function (): void {
    $config = new ProjectConfig(collect([
        new NamespaceResolver('App\\', '/project/app'),
    ]));

    $result = new ProjectConfigLoadResult($config, null);

    expect($result->isSuccess())->toBeTrue()
        ->and($result->config)->toBe($config)
        ->and($result->error)->toBeNull();
});

it('reports failure when error is present', function (): void {
    $result = new ProjectConfigLoadResult(null, 'Resolver prefix must not be empty.');

    expect($result->isSuccess())->toBeFalse()
        ->and($result->config)->toBeNull()
        ->and($result->error)->toBe('Resolver prefix must not be empty.');
});
