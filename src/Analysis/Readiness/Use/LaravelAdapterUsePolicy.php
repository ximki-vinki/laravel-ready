<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;

final readonly class LaravelAdapterUsePolicy extends UsePolicy
{
    private const array ALLOWED_DEPENDENCY_LEVELS = [
        ReadinessLevel::LaravelAdapter, // @pest-mutate-ignore: RemoveArrayItem
    ];

    public function __construct(private Psr4ClassLocator $locator) {}

    protected function rules(): array
    {
        return [
            new DenyAppImportByLevelRule(
                $this->locator,
                self::ALLOWED_DEPENDENCY_LEVELS,
            ),
        ];
    }
}
