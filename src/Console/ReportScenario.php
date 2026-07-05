<?php

declare(strict_types=1);

namespace LaravelReady\Console;

enum ReportScenario
{
    case Clean;
    case LegacyInfo;
    case GuardFailed;
    case TagInvalid;
}
