<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessResult;
use LaravelReady\Console\HeaderStyle;

final class ReadinessHeader
{
    public static function format(string $relativePath, ReadinessResult $readiness, HeaderStyle $style): string
    {
        $line = $relativePath.' : '.$readiness->actual->value;

        return match ($style) {
            HeaderStyle::Error => '<error>'.$line.'</>',
            HeaderStyle::Warning => '<fg=white;bg=yellow>'.$line.'</>',
            HeaderStyle::Clean => match ($readiness->actual) {
                ReadinessLevel::Legacy => '<fg=yellow>'.$line.'</>',
                ReadinessLevel::LegacyAdapter => '<fg=cyan>'.$line.'</>',
                ReadinessLevel::LaravelAdapter => '<fg=bright-cyan>'.$line.'</>',
                ReadinessLevel::LegacyPerfect => '<fg=green>'.$line.'</>',
                ReadinessLevel::LaravelReady => '<fg=bright-green>'.$line.'</>',
                default => $line,
            },
        };
    }
}
