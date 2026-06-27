<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

interface Finding
{
    public function display(): string;
}
