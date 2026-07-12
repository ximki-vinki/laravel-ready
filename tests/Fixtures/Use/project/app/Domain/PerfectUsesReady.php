<?php

namespace App\Domain;

use App\Domain\TaggedService;

/** @legacy-perfect */
class PerfectUsesReady
{
    public function __construct(private TaggedService $service) {}
}
