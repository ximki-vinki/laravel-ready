<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Visitors\BlockedFunctionVisitor;
use LaravelReady\Analysis\Visitors\GlobalVisitor;
use LaravelReady\Analysis\Visitors\SuperglobalVisitor;
use LaravelReady\Analysis\Visitors\TagVisitor;
use LaravelReady\Analysis\Visitors\UseVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

final class Detector
{
    public function analyse(string $path): AnalysisResult
    {
        return $path
            |> $this->readCode(...)
            |> $this->parseCode(...)
            |> $this->analyseAst(...);
    }

    private function readCode(string $path): ?string
    {
        if (! is_file($path)) {
            return null; // @pest-mutate-ignore: RemoveEarlyReturn
        }

        $code = file_get_contents($path);

        return $code === false ? null : $code; // @pest-mutate-ignore: FalseToTrue
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
     */
    private function analyseAst(?array $ast): AnalysisResult
    {
        /** @var Collection<array-key, Finding> $findings */
        $findings = collect();

        if ($ast !== null) {
            $traverser = new NodeTraverser;
            $traverser->addVisitor(new TagVisitor($findings));
            $traverser->addVisitor(new SuperglobalVisitor($findings));
            $traverser->addVisitor(new GlobalVisitor($findings));
            $traverser->addVisitor(new BlockedFunctionVisitor($findings));
            $traverser->addVisitor(new UseVisitor($findings));
            $traverser->traverse($ast);
        }

        return new AnalysisResult(
            findings: $findings,
        );
    }
}
