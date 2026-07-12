<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Findings;

final readonly class UseFinding implements Finding
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
