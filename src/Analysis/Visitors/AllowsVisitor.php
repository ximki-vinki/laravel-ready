<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use LaravelReady\Analysis\Allows\AllowsParser;
use LaravelReady\Analysis\Allows\AllowsParseResult;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class AllowsVisitor extends NodeVisitorAbstract
{
    public private(set) ?AllowsParseResult $allows = null;

    public function __construct(private readonly AllowsParser $parser = new AllowsParser) {}

    public function enterNode(Node $node): ?int
    {
        if ($this->allows instanceof AllowsParseResult) {
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

        $this->allows = $parsed;

        return null;
    }
}
