<?php

declare(strict_types=1);

namespace LaravelReady\Project;

use Illuminate\Support\Collection;

final readonly class ProjectConfig
{
    /**
     * @param  Collection<int, NamespaceResolver>  $resolvers
     */
    public function __construct(public Collection $resolvers) {}
}
