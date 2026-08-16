<?php

declare(strict_types=1);

use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\NamespaceResolver;
use LaravelReady\Project\ProjectConfig;

covers(Psr4ClassLocator::class);

it('locates class file for a matching prefix', function (): void {
    $root = projectRoot().'/tests/Fixtures/Locator';
    $locator = new Psr4ClassLocator(new ProjectConfig(collect([
        new NamespaceResolver('App\\', $root.'/project/app'),
    ])));

    expect($locator->locate('App\Domain\Foo'))
        ->toBe($root.'/project/app/Domain/Foo.php');
});

it('returns null for an unknown prefix', function (): void {
    $root = projectRoot().'/tests/Fixtures/Locator';
    $locator = new Psr4ClassLocator(new ProjectConfig(collect([
        new NamespaceResolver('App\\', $root.'/project/app'),
    ])));

    expect($locator->locate('Vendor\Package\Service'))->toBeNull();
});

it('returns null when prefix matches but file is missing', function (): void {
    $root = projectRoot().'/tests/Fixtures/Locator';
    $locator = new Psr4ClassLocator(new ProjectConfig(collect([
        new NamespaceResolver('App\\', $root.'/project/app'),
    ])));

    expect($locator->locate('App\Domain\Missing'))->toBeNull();
});

it('locates classes under two different prefixes', function (): void {
    $root = projectRoot().'/tests/Fixtures/Locator';
    $locator = new Psr4ClassLocator(new ProjectConfig(collect([
        new NamespaceResolver('App\\', $root.'/project/app'),
        new NamespaceResolver('Domain\\', $root.'/project/domain'),
    ])));

    expect($locator->locate('App\Domain\Foo'))
        ->toBe($root.'/project/app/Domain/Foo.php')
        ->and($locator->locate('Domain\Bar'))
        ->toBe($root.'/project/domain/Bar.php');
});

it('locates class php by default', function (): void {
    expect(appLocator()->locate('App\Domain\LegacyDto'))
        ->toBe(appRoot().'/Domain/LegacyDto.class.php');
});
