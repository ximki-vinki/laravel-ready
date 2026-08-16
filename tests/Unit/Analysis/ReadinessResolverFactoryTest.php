<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessResolverFactory;

covers(ReadinessResolverFactory::class);

it('creates a resolver configured for the project', function (): void {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LegacyAdapter, 3),
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    $readiness = (new ReadinessResolverFactory)
        ->create(appProjectConfig())
        ->resolve($result);

    expect($readiness->findings)
        ->toContainEqual(new UseFinding('App\Domain\TaggedService', 5));
});
