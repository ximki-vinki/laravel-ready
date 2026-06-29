<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\GlobalFinding;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PhpParser\NodeVisitorAbstract;

final class GlobalVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof Global_) {
            return null;
        }

        foreach ($node->vars as $variable) {
            if (! $variable instanceof Variable || ! is_string($variable->name)) {
                continue;
            }

            $this->findings->push(new GlobalFinding(
                $variable->name,
                $variable->getStartLine(),
            ));
        }

        return null;
    }
}
