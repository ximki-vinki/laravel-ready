<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final readonly class SuperglobalFinding implements Finding
{
    public function __construct(
        public SuperglobalName $name,
        public int $line,
    ) {}

    public function display(): string
    {
        return '$'.$this->name->value.' (line '.$this->line.')';
    }
}
