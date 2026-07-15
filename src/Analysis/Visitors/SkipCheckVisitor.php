<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Visitors;

use Illuminate\Support\Str;
use LaravelReady\Analysis\Enums\DocModifier;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipCheckVisitor extends NodeVisitorAbstract
{
    public private(set) bool $detected = false;

    public function enterNode(Node $node): ?int
    {
        $docComment = $node->getDocComment()?->getText();

        if ($docComment !== null && Str::contains($docComment, DocModifier::SkipCheck->value)) {
            $this->detected = true;
        }

        return null;
    }
}
