<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use LaravelReady\Analysis\SkipCheck\SkipCheckParser;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipCheckVisitor extends NodeVisitorAbstract
{
    public private(set) bool $detected = false;

    public private(set) ?string $date = null;

    public function __construct(private readonly SkipCheckParser $parser = new SkipCheckParser) {}

    public function enterNode(Node $node): ?int
    {
        if ($this->detected) {
            return null;
        }

        $docComment = $node->getDocComment()?->getText();

        if ($docComment === null) {
            return null;
        }

        $parsed = $this->parser->parse($docComment);

        if (! $parsed instanceof SkipCheckParseResult) {
            return null;
        }

        $this->detected = true;
        $this->date = $parsed->date;

        return null;
    }
}
