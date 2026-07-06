<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Findings;

final readonly class GlobalFinding implements LegacyFinding
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
