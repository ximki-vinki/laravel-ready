<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final readonly class FunctionCallFinding implements Finding
{
    public function __construct(
        public BlockedFunction $function,
        public int $line,
    ) {}

    public function display(): string
    {
        return $this->function->value.'() (line '.$this->line.')';
    }
}
