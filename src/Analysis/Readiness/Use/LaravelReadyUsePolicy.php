<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyWfNamespaceRule;

final readonly class LaravelReadyUsePolicy extends UsePolicy
{
    private const array ALLOWED_DEPENDENCY_LEVELS = [
        ReadinessLevel::LaravelReady,   // @pest-mutate-ignore: RemoveArrayItem
        ReadinessLevel::LaravelAdapter, // @pest-mutate-ignore: RemoveArrayItem
    ];

    public function __construct(private string $appRoot) {}

    protected function rules(): array
    {
        return [
            new DenyWfNamespaceRule,
            new DenyAppImportByLevelRule(
                $this->appRoot,
                self::ALLOWED_DEPENDENCY_LEVELS,
            ),
        ];
    }
}
