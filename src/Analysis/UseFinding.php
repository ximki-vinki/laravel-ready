<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final readonly class UseFinding implements LegacyFinding
{
    public function __construct(
        public string $fqcn,
        public int $line,
    ) {}

    public function display(): string
    {
        return $this->fqcn.' (line '.$this->line.')';
    }
}
