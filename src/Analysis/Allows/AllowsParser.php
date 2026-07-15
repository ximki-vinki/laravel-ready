<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Allows;

use Illuminate\Support\Str;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\DocModifier;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

final class AllowsParser
{
    public function parseAllows(string $docComment, int $startLine): ?AllowsParseResult
    {
        if (! Str::contains($docComment, DocModifier::Allows->value)) {
            return null; // @pest-mutate-ignore: RemoveEarlyReturn
        }

        foreach (explode("\n", $docComment) as $offset => $line) {
            $line = trim($line, " \t*/");

            if (! Str::contains($line, DocModifier::Allows->value)) {
                continue;
            }

            return $this->parseLine(
                Str::after($line, DocModifier::Allows->value),
                $startLine + $offset,
            );
        }

        return null;
    }

    public function parseLine(string $rest, int $line): AllowsParseResult
    {
        return collect(explode(',', $rest))
            ->map(fn (string $raw): string => trim($raw))
            ->filter()
            ->reduce(
                function (AllowsParseResult $result, string $token) use ($line): AllowsParseResult {
                    $resolved = $this->resolveToken($token);

                    return $resolved === null
                        ? $result->withUnknown(new UnknownAllowTokenFinding($token, $line))
                        : $result->withToken($resolved);
                },
                AllowsParseResult::empty(),
            );
    }

    private function resolveToken(string $token): SuperglobalName|BlockedFunction|AllowKeyword|null
    {
        if (Str::startsWith($token, '$')) {
            return SuperglobalName::tryFrom(substr($token, 1));
        }

        return AllowKeyword::tryFrom($token)
            ?? BlockedFunction::tryFrom($token);
    }
}
