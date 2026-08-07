<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use LaravelReady\ContainerFactory;
use LaravelReady\Project\ProjectConfig;

final readonly class AnalyseRunnerFactory
{
    public function __construct(private ContainerFactory $containerFactory) {}

    public function create(ProjectConfig $config): AnalyseRunner
    {
        /** @var AnalyseRunner $runner */
        $runner = $this->containerFactory->create($config)->get(AnalyseRunner::class);

        return $runner;
    }
}
