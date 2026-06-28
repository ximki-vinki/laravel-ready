<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\FunctionCallFinding;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

final class BlockedFunctionVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof FuncCall || ! $node->name instanceof Name) {
            return null;
        }

        $function = BlockedFunction::tryFrom($node->name->toString());

        if ($function === null) {
            return null;
        }

        $this->findings->push(new FunctionCallFinding(
            $function,
            $node->getStartLine(),
        ));

        return null;
    }
}
