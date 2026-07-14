<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Findings;

final readonly class UnknownAllowTokenFinding implements Finding
{
    public function __construct(
        public string $token,
        public int $line,
    ) {}

    public function display(): string
    {
        return $this->token.' (line '.$this->line.')';
    }
}
