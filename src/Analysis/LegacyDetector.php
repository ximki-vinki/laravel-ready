<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeFinder;
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

        $variables = (new NodeFinder)->findInstanceOf($ast, Variable::class);

        foreach ($variables as $variable) {
            if (! is_string($variable->name)) {
                continue;
            }

            $superglobal = SuperglobalName::tryFrom($variable->name);

            if ($superglobal === null) {
                continue;
            }

            $findings->push(new SuperglobalFinding(
                $superglobal,
                $variable->getStartLine(),
            ));
        }

        return $findings;
    }
}
