<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Allows\AllowsParser;
use LaravelReady\Analysis\Allows\AllowsParseResult;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\Finding;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class AllowsVisitor extends NodeVisitorAbstract
{
    /** @var Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword>|null */
    public private(set) ?Collection $allows = null;

    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(
        private readonly Collection $findings,
        private readonly AllowsParser $parser = new AllowsParser,
    ) {}

    public function enterNode(Node $node): ?int
    {
        if ($this->allows instanceof Collection) {
            return null;
        }

        $docComment = $node->getDocComment();

        if (! $docComment instanceof Doc) {
            return null;
        }

        $parsed = $this->parser->parseAllows(
            $docComment->getText(),
            $docComment->getStartLine(),
        );

        if (! $parsed instanceof AllowsParseResult) {
            return null;
        }

        $this->findings->push(...$parsed->unknowns);
        $this->allows = $parsed->tokens->values();

        return null;
    }
}
