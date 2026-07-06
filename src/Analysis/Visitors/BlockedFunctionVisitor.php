<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\FunctionCallFinding;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;

final class BlockedFunctionVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Eval_) {
            $this->findings->push(new FunctionCallFinding(
                BlockedFunction::Eval,
                $node->getStartLine(),
            ));

            return null;
        }

        if (! $node instanceof FuncCall || ! $node->name instanceof Name) {
            return null;
        }

        $function = BlockedFunction::tryFrom($node->name->toString());

        if ($function === null) {
            return null;
        }

        if ($function === BlockedFunction::ParseStr && count($node->args) !== 1) {
            return null;
        }

        if ($function === BlockedFunction::Assert && ! $this->assertHasStringArgument($node)) {
            return null;
        }

        $this->findings->push(new FunctionCallFinding(
            $function,
            $node->getStartLine(),
        ));

        return null;
    }

    private function assertHasStringArgument(FuncCall $node): bool
    {
        if ($node->args === []) {
            return false;
        }

        $firstArg = $node->args[0];

        return $firstArg instanceof Arg && $firstArg->value instanceof String_;
    }
}
