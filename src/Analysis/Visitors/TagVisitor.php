<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\TagFinding;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class TagVisitor extends NodeVisitorAbstract
{
    /** @param  Collection<array-key, Finding>  $findings */
    public function __construct(private readonly Collection $findings) {}

    public function enterNode(Node $node): ?int
    {
        $docComment = $node->getDocComment()?->getText();
        $tag = $docComment !== null ? Tag::tryFromDocComment($docComment) : null;

        if ($tag !== null) {
            $this->findings->push(new TagFinding($tag, $node->getStartLine()));
        }

        return null;
    }
}
