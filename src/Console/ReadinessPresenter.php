<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\Output\FindingsOutput;
use LaravelReady\Console\Output\HeaderOutput;
use LaravelReady\Console\Output\ReadinessFooterOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ReadinessPresenter
{
    public function present(ReadinessResult $readiness, string $relativePath, OutputInterface $output): int
    {
        $plan = (new PresentationPlanBuilder)->build($readiness);

        (new HeaderOutput)->write($output, $readiness, $relativePath, $plan->headerStyle);

        if ($plan->showFindings) {
            (new FindingsOutput)->write($output, $readiness);
        }

        if ($plan->footer !== null) {
            (new ReadinessFooterOutput)->write($output, $plan->footer);
        }

        return $plan->exitCode;
    }
}
