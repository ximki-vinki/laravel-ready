<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;

final readonly class LegacyPerfectUsePolicy extends UsePolicy
{
    private const array ALLOWED_DEPENDENCY_LEVELS = [
        ReadinessLevel::LegacyAdapter, // @pest-mutate-ignore: RemoveArrayItem
        ReadinessLevel::LegacyPerfect, // @pest-mutate-ignore: RemoveArrayItem
    ];

    public function __construct(private string $appRoot) {}

    protected function rules(): array
    {
        return [
            new DenyAppImportByLevelRule(
                $this->appRoot,
                self::ALLOWED_DEPENDENCY_LEVELS,
            ),
        ];
    }
}
