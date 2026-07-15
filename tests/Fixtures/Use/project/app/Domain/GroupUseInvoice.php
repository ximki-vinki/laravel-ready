<?php

namespace App\Domain;

use Wf\Legacy\{OldRepo};

/** @laravel-ready */
class GroupUseInvoice
{
    public function __construct(private OldRepo $repo) {}
}
