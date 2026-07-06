<?php

namespace App\Consumer;

use App\Domain\UntaggedService;

/** @laravel-ready */
class UsesUntagged
{
    public function __construct(private UntaggedService $service) {}
}
