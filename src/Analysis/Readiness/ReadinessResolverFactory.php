<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\Readiness\Use\UsePolicyFactory;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\ProjectConfig;

final readonly class ReadinessResolverFactory
{
    public function create(ProjectConfig $config): ReadinessResolver
    {
        $locator = new Psr4ClassLocator($config->resolvers);
        $policyFactory = new UsePolicyFactory($locator);

        return new ReadinessResolver(
            new UseDependencyChecker($policyFactory),
        );
    }
}
