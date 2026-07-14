<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class AllowsVisitor extends NodeVisitorAbstract
{
    /** @var Collection<array-key, SuperglobalName|BlockedFunction>|null */
    public private(set) ?Collection $allows = null;

    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        $docComment = $node->getDocComment();

        if ($docComment === null || ! Str::contains($docComment->getText(), '@allows')) {
            return null;
        }

        foreach (explode("\n", $docComment->getText()) as $offset => $line) {
            $line = trim($line, " \t*/");

            if (! Str::startsWith($line, '@allows')) {
                continue;
            }

            $this->mergeTokens(
                trim(Str::after($line, '@allows')),
                $docComment->getStartLine() + $offset,
            );
        }

        return null;
    }

    private function mergeTokens(string $rest, int $line): void
    {
        /** @var Collection<array-key, SuperglobalName|BlockedFunction> $parsed */
        $parsed = collect();

        if ($rest !== '') {
            foreach (explode(',', $rest) as $raw) {
                $token = trim($raw);

                if ($token === '') {
                    continue;
                }

                $resolved = $this->resolveToken($token);

                if ($resolved === null) {
                    $this->findings->push(new UnknownAllowTokenFinding($token, $line));

                    continue;
                }

                $parsed->push($resolved);
            }
        }

        if ($this->allows === null) {
            $this->allows = $parsed;

            return;
        }

        $this->allows = $this->allows->concat($parsed)->values();
    }

    private function resolveToken(string $token): SuperglobalName|BlockedFunction|null
    {
        if (Str::startsWith($token, '$')) {
            return SuperglobalName::tryFrom(substr($token, 1));
        }

        return BlockedFunction::tryFrom($token);
    }
}
