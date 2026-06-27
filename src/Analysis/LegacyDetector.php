<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\ParserFactory;

final class LegacyDetector
{
    public function isLegacy(string $path): ?string
    {
        return $path
            |> $this->readCode(...)
            |> $this->parseCode(...)
            |> $this->findBlocker(...);
    }

    private function readCode(string $path): ?string
    {
        $code = file_get_contents($path);

        return $code === false ? null : $code;
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
    private function findBlocker(?array $ast): ?string
    {
        if ($ast === null) {
            return null;
        }

        foreach ($ast as $node) {
            if (! $node instanceof Expression) {
                continue;
            }

            if ($node->expr instanceof Variable
                && $node->expr->name === SuperglobalName::Globals->value) {
                return SuperglobalName::Globals->value;
            }
        }

        return null;
    }
}
