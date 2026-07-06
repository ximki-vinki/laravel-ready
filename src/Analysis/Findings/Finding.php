<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Findings;

interface Finding
{
    public function display(): string;
}
