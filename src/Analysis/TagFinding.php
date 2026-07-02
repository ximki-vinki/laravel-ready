<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final readonly class TagFinding implements Finding
{
    public function __construct(
        public Tag $tag,
        public int $line,
    ) {}

    public function display(): string
    {
        return 'tag: @'.$this->tag->value.' (line '.$this->line.')';
    }
}
