<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

use Illuminate\Support\Str;
use LaravelReady\Analysis\Enums\DocModifier;

final class SkipCheckParser
{
    public function parse(string $docComment): ?SkipCheckParseResult
    {
        if (! Str::contains($docComment, DocModifier::SkipCheck->value)) {
            return null; // @pest-mutate-ignore: RemoveEarlyReturn
        }

        foreach (explode("\n", $docComment) as $line) {
            $line = trim($line, " \t*/"); // @pest-mutate-ignore: UnwrapTrim

            if (preg_match('/@skipCheck(?:\(([^)]*)\))?(?![A-Za-z0-9_])/', $line, $matches) !== 1) {
                continue;
            }

            $argument = trim($matches[1] ?? '');

            return new SkipCheckParseResult($argument === '' ? null : $argument);
        }

        return null;
    }
}
