<?php

namespace App\Consumer;

use App\Domain\TaggedService;

/** @laravel-ready */
class UsesTagged
{
    public function __construct(private TaggedService $service) {}
}
