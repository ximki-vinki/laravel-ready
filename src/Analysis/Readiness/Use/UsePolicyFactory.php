<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;

final readonly class UsePolicyFactory
{
    public function __construct(
        private Psr4ClassLocator $locator,
    ) {}

    public function create(ReadinessLevel $level): ?UsePolicy
    {
        return match ($level) {
            ReadinessLevel::LaravelReady => new LaravelReadyUsePolicy($this->locator),
            ReadinessLevel::LaravelAdapter => new LaravelAdapterUsePolicy($this->locator),
            ReadinessLevel::LegacyAdapter => new LegacyAdapterUsePolicy($this->locator),
            ReadinessLevel::LegacyPerfect => new LegacyPerfectUsePolicy($this->locator),
            default => null,
        };
    }
}
