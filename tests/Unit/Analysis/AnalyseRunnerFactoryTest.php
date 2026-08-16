<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalyseRunner;
use LaravelReady\Analysis\AnalyseRunnerFactory;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\ContainerFactory;

covers(AnalyseRunnerFactory::class, AnalyseRunner::class);

it('creates runner that analyses a laravel-ready fixture', function (): void {
    $runner = new AnalyseRunnerFactory(new ContainerFactory)->create(appConfig());

    $readiness = $runner->analyse(appRoot().'/Domain/TaggedService.php');

    expect($runner)->toBeInstanceOf(AnalyseRunner::class)
        ->and($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->hasBlockers)->toBeFalse();
});
