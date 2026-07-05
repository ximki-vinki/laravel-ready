<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;

final class ReadinessHeader
{
    public static function format(string $relativePath, ReadinessResult $readiness): string
    {
        $line = $relativePath.' : '.$readiness->actual->value;

        if ($readiness->hasBlockers) {
            return '<error>'.$line.'</>';
        }

        return match ($readiness->actual) {
            ReadinessLevel::Legacy => '<fg=yellow>'.$line.'</>',
            ReadinessLevel::LegacyPerfect => '<fg=green>'.$line.'</>',
            ReadinessLevel::LaravelAdapter => '<fg=cyan>'.$line.'</>',
            ReadinessLevel::LaravelReady => '<fg=bright-green>'.$line.'</>',
            ReadinessLevel::LaravelPerfect => '<fg=bright-cyan>'.$line.'</>',
            default => $line,
        };
    }
}
