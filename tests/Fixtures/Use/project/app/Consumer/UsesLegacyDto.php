<?php

namespace App\Consumer;

use App\Domain\LegacyDto;

/** @laravel-ready */
class UsesLegacyDto
{
    public function __construct(private LegacyDto $dto) {}
}
