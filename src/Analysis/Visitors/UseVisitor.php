<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

final class UseVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        if (! $node instanceof Use_) {
            return null;
        }

        foreach ($node->uses as $use) {
            $this->findings->push(new UseImportFinding(
                $use->name->toString(),
                $use->getStartLine(),
            ));
        }

        return null;
    }
}
