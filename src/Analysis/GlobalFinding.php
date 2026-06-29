<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final readonly class GlobalFinding implements Finding
{
    public function __construct(
        public string $variable,
        public int $line,
    ) {}

    public function display(): string
    {
        return '$'.$this->variable.' (line '.$this->line.')';
    }
}
