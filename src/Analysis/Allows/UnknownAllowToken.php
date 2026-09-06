<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Allows;

use LaravelReady\Analysis\Displayable;

final readonly class UnknownAllowToken implements Displayable
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
