<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeVisitorAbstract;

final class SuperglobalVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof Variable || ! is_string($node->name)) {
            return null;
        }

        $superglobal = SuperglobalName::tryFrom($node->name);

        if ($superglobal === null) {
            return null;
        }

        $this->findings->push(new SuperglobalFinding(
            $superglobal,
            $node->getStartLine(),
        ));

        return null;
    }
}
