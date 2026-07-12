<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

enum ReadinessLevel: string
{
    case Untagged = 'Untagged';
    case MultiTag = 'MultiTag';
    case Legacy = 'Legacy';
    case LegacyAdapter = 'LegacyAdapter';
    case LegacyPerfect = 'LegacyPerfect';
    case LaravelAdapter = 'LaravelAdapter';
    case LaravelReady = 'LaravelReady';
    case LaravelPerfect = 'LaravelPerfect';
}
