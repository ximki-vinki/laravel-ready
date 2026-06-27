<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\ParserFactory;

final class LegacyDetector
{
    /** @return Collection<array-key, SuperglobalFinding> */
    public function analyse(string $path): Collection
    {
        return $path
            |> $this->readCode(...)
            |> $this->parseCode(...)
            |> $this->findSuperglobals(...);
    }

    private function readCode(string $path): ?string
    {
        if (! is_file($path)) {
            return null; // @pest-mutate-ignore
        }

        $code = file_get_contents($path);

        return $code === false ? null : $code; // @pest-mutate-ignore
    }

    /**
     * @return array<Node\Stmt>|null
     */
    private function parseCode(?string $code): ?array
    {
        if ($code === null) {
            return null;
        }

        $ast = (new ParserFactory)
            ->createForNewestSupportedVersion()
            ->parse($code);

        return is_array($ast) ? $ast : null;
    }

    /**
     * @param  array<Node\Stmt>|null  $ast
     * @return Collection<array-key, SuperglobalFinding>
     */
    private function findSuperglobals(?array $ast): Collection
    {
        $findings = collect();

        if ($ast === null) {
            return $findings; // @pest-mutate-ignore
        }

        foreach ($ast as $node) {
            if (! $node instanceof Expression) {
                continue;
            }

            if ($node->expr instanceof Variable
                && $node->expr->name === SuperglobalName::Globals->value) {
                $findings->push(new SuperglobalFinding(
                    SuperglobalName::Globals,
                    $node->expr->getStartLine(),
                ));
            }
        }

        return $findings;
    }
}
