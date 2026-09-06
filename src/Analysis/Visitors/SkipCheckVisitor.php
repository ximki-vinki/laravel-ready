<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use LaravelReady\Analysis\SkipCheck\SkipCheckParser;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipCheckVisitor extends NodeVisitorAbstract
{
    public private(set) ?SkipCheckParseResult $skipCheck = null;

    public function __construct(private readonly SkipCheckParser $parser = new SkipCheckParser) {}

    public function enterNode(Node $node): ?int
    {
        if ($this->skipCheck instanceof SkipCheckParseResult) {
            return null;
        }

        $docComment = $node->getDocComment();

        if (! $docComment instanceof Doc) {
            return null;
        }

        $parsed = $this->parser->parse($docComment->getText());

        if (! $parsed instanceof SkipCheckParseResult) {
            return null;
        }

        $this->skipCheck = $parsed;

        return null;
    }
}
