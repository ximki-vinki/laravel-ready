<?php

namespace App\Domain;

use App\Legacy\OldRepo;

/** @laravel-ready */
class Invoice
{
    public function __construct(private OldRepo $repo) {}
}
