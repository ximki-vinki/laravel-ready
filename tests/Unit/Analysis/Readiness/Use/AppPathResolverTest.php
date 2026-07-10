<?php

declare(strict_types=1);

use LaravelReady\Analysis\Readiness\Use\AppPathResolver;

covers(AppPathResolver::class);

it('matches app namespace', function () {
    expect(AppPathResolver::matches('App\Domain\Foo'))->toBeTrue();
});

it('does not match non-app fqcn', function () {
    expect(AppPathResolver::matches('Illuminate\Support\Collection'))->toBeFalse();
});

it('resolves existing php file', function () {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\TaggedService'))
        ->toBe(appRoot().'/Domain/TaggedService.php');
});

it('returns null when file is missing', function () {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\NonExistent'))->toBeNull();
});

it('tries extensions in order', function () {
    $resolver = new AppPathResolver(appRoot(), ['.php', '.class.php']);

    expect($resolver->resolve('App\Domain\LegacyDto'))
        ->toBe(appRoot().'/Domain/LegacyDto.class.php');
});

it('returns null when only wrong extension is configured', function () {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\LegacyDto'))->toBeNull();
});
