<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Allows\AllowListParser;
use LaravelReady\Analysis\Allows\AllowListParseResult;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\Finding;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class AllowsVisitor extends NodeVisitorAbstract
{
    /** @var Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword>|null */
    public private(set) ?Collection $allows = null;

    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(
        private readonly Collection $findings,
        private readonly AllowListParser $parser = new AllowListParser,
    ) {}

    public function enterNode(Node $node): ?int
    {
        $docComment = $node->getDocComment();

        if ($docComment === null) {
            return null;
        }

        $parsed = $this->parser->parseDocComment(
            $docComment->getText(),
            $docComment->getStartLine(),
        );

        if ($parsed === null) {
            return null;
        }

        foreach ($parsed->unknowns as $unknown) {
            $this->findings->push($unknown);
        }

        $this->allows = ($this->allows ?? collect())
            ->concat($parsed->tokens)
            ->values();

        return null;
    }
}
