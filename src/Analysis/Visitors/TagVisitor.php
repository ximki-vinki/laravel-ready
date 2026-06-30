<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use LaravelReady\Analysis\Tag;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class TagVisitor extends NodeVisitorAbstract
{
    private ?Tag $tag = null;

    public function tag(): ?Tag
    {
        return $this->tag;
    }

    public function enterNode(Node $node): ?int
    {
        if ($this->tag !== null) {
            return null;
        }

        $docComment = $node->getDocComment()?->getText();
        $this->tag = $docComment !== null ? Tag::tryFromDocComment($docComment) : null;

        return null;
    }
}
