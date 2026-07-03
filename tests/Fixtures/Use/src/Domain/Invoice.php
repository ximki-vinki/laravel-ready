<?php

namespace App\Domain;

use Wf\Legacy\OldRepo;

/** @laravel-ready */
class Invoice
{
    public function __construct(private OldRepo $repo) {}
}
