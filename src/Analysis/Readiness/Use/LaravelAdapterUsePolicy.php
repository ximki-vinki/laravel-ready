<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\NamespaceResolver;

final readonly class LaravelAdapterUsePolicy extends UsePolicy
{
    private const array ALLOWED_DEPENDENCY_LEVELS = [
        ReadinessLevel::LaravelAdapter, // @pest-mutate-ignore: RemoveArrayItem
    ];

    public function __construct(private string $appRoot) {}

    protected function rules(): array
    {
        return [
            new DenyAppImportByLevelRule(
                new Psr4ClassLocator(collect([
                    new NamespaceResolver('App\\', $this->appRoot),
                ])),
                self::ALLOWED_DEPENDENCY_LEVELS,
            ),
        ];
    }
}
