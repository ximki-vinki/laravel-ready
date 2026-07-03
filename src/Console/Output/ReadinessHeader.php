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
            ReadinessLevel::LaravelReady => '<fg=green>'.$line.'</>',
            ReadinessLevel::Legacy => '<fg=yellow>'.$line.'</>',
            default => $line,
        };
    }
}
