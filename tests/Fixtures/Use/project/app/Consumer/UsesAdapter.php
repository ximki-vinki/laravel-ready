<?php

namespace App\Consumer;

use App\Adapter\WfGateway;

/** @laravel-ready */
class UsesAdapter
{
    public function __construct(private WfGateway $gateway) {}
}
