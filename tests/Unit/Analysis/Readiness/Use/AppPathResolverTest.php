<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Readiness\Use\AppPathResolver;

covers(AppPathResolver::class);

it('matches app namespace', function (): void {
    expect(AppPathResolver::matches('App\Domain\Foo'))->toBeTrue();
});

it('does not match non-app fqcn', function (): void {
    expect(AppPathResolver::matches(Collection::class))->toBeFalse();
});

it('resolves existing php file', function (): void {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\TaggedService'))
        ->toBe(appRoot().'/Domain/TaggedService.php');
});

it('returns null when file is missing', function (): void {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\NonExistent'))->toBeNull();
});

it('tries extensions in order', function (): void {
    $resolver = new AppPathResolver(appRoot(), ['.php', '.class.php']);

    expect($resolver->resolve('App\Domain\LegacyDto'))
        ->toBe(appRoot().'/Domain/LegacyDto.class.php');
});

it('returns null when only wrong extension is configured', function (): void {
    $resolver = new AppPathResolver(appRoot(), ['.php']);

    expect($resolver->resolve('App\Domain\LegacyDto'))->toBeNull();
});
