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
            HeaderStyle::Warning => '<fg=yellow>'.$line.'</>',
            HeaderStyle::Clean => match ($readiness->actual) {
                ReadinessLevel::LegacyPerfect => '<fg=green>'.$line.'</>',
                // TODO LaravelPerfect => '<fg=bright-cyan>'.$line.'</>',
                ReadinessLevel::LegacyAdapter => '<fg=yellow>'.$line.'</>',
                ReadinessLevel::LaravelAdapter => '<fg=cyan>'.$line.'</>',
                ReadinessLevel::LaravelReady => '<fg=bright-green>'.$line.'</>',
                default => $line,
            },
        };
    }
}
