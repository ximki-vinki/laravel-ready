<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalyseRunner;
use LaravelReady\Analysis\Readiness\ReadinessResolver;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\ContainerFactory;

covers(ContainerFactory::class);

it('builds analyse runner with injected locator config', function (): void {
    $container = new ContainerFactory()->create(appConfig());

    $runner = $container->get(AnalyseRunner::class);
    $locator = $container->get(Psr4ClassLocator::class);

    expect($runner)->toBeInstanceOf(AnalyseRunner::class)
        ->and($container->get(ReadinessResolver::class))->toBeInstanceOf(ReadinessResolver::class)
        ->and($locator)->toBeInstanceOf(Psr4ClassLocator::class)
        ->and($locator->locate('App\Domain\Invoice'))->not->toBeNull();
});
