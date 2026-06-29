<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Visitors\BlockedFunctionVisitor;
use LaravelReady\Analysis\Visitors\GlobalVisitor;
use LaravelReady\Analysis\Visitors\SuperglobalVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

final class LegacyDetector
{
    /** @return Collection<array-key, Finding> */
    public function analyse(string $path): Collection
    {
        return $path
            |> $this->readCode(...)
            |> $this->parseCode(...)
            |> $this->findFindings(...);
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
     * @return Collection<array-key, Finding>
     */
    private function findFindings(?array $ast): Collection
    {
        /** @var Collection<array-key, Finding> $findings */
        $findings = collect();

        if ($ast === null) {
            return $findings;
        }

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new SuperglobalVisitor($findings));
        $traverser->addVisitor(new GlobalVisitor($findings));
        $traverser->addVisitor(new BlockedFunctionVisitor($findings));
        $traverser->traverse($ast);

        return $findings;
    }
}
