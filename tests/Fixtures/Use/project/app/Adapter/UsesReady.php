<?php

namespace App\Adapter;

use App\Domain\TaggedService;

/** @legacy-adapter */
class UsesReady
{
    public function __construct(private TaggedService $service) {}
}
