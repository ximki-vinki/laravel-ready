<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Allows;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

final class AllowListParser
{
    /**
     * @return AllowListParseResult|null null, если тега @allows нет
     */
    public function parseDocComment(string $docComment, int $startLine): ?AllowListParseResult
    {
        if (! Str::contains($docComment, '@allows')) {
            return null;
        }

        /** @var Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword> $tokens */
        $tokens = collect();
        /** @var Collection<array-key, UnknownAllowTokenFinding> $unknowns */
        $unknowns = collect();

        foreach (explode("\n", $docComment) as $offset => $line) {
            $line = trim($line, " \t*/");

            if (! Str::contains($line, '@allows')) {
                continue;
            }

            $parsed = $this->parseLine(
                trim(Str::after($line, '@allows')),
                $startLine + $offset,
            );

            $tokens = $tokens->concat($parsed->tokens);
            $unknowns = $unknowns->concat($parsed->unknowns);
        }

        return new AllowListParseResult($tokens->values(), $unknowns->values());
    }

    public function parseLine(string $rest, int $line): AllowListParseResult
    {
        /** @var Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword> $tokens */
        $tokens = collect();
        /** @var Collection<array-key, UnknownAllowTokenFinding> $unknowns */
        $unknowns = collect();

        if ($rest === '') {
            return new AllowListParseResult($tokens, $unknowns);
        }

        foreach (explode(',', $rest) as $raw) {
            $token = trim($raw);

            if ($token === '') {
                continue;
            }

            $resolved = $this->resolveToken($token);

            if ($resolved === null) {
                $unknowns->push(new UnknownAllowTokenFinding($token, $line));

                continue;
            }

            $tokens->push($resolved);
        }

        return new AllowListParseResult($tokens, $unknowns);
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
