<?php

use LaravelReady\Analysis\Readiness\ReadinessResolver;
use LaravelReady\Analysis\Readiness\UseDependencyChecker;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\NamespaceResolver;
use LaravelReady\Project\ProjectConfig;
use PHPUnit\Framework\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

require __DIR__.'/../vendor/autoload.php';

pest()->extend(TestCase::class)->in('Unit', 'Feature');

function projectRoot(): string
{
    return dirname(__DIR__);
}

function appRoot(): string
{
    return projectRoot().'/tests/Fixtures/Use/project/app';
}

function appConfig(): ProjectConfig
{
    return new ProjectConfig(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
}

function appLocator(): Psr4ClassLocator
{
    return new Psr4ClassLocator(appConfig());
}

function readinessResolver(): ReadinessResolver
{
    return new ReadinessResolver(new UseDependencyChecker(appLocator()));
}
