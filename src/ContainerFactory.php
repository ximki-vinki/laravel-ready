<?php

declare(strict_types=1);

namespace LaravelReady;

use LaravelReady\Analysis\AnalyseRunner;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Readiness\ReadinessResolver;
use LaravelReady\Analysis\Readiness\UseDependencyChecker;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\ProjectConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerFactory
{
    public function create(ProjectConfig $config): ContainerInterface
    {
        $container = new ContainerBuilder;

        $container->register(ProjectConfig::class)
            ->setSynthetic(true)
            ->setPublic(true);

        foreach ([
            Psr4ClassLocator::class,
            UseDependencyChecker::class,
            ReadinessResolver::class,
            Detector::class,
            AnalyseRunner::class,
        ] as $service) {
            $container->register($service)
                ->setAutowired(true)
                ->setPublic(true);
        }

        $container->compile();

        $container->set(ProjectConfig::class, $config);

        return $container;
    }
}
