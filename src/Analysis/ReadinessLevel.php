<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

enum ReadinessLevel: string
{
    case Untagged = 'Untagged';
    case Legacy = 'Legacy';
    case LegacyPerfect = 'LegacyPerfect';
    case LaravelReady = 'LaravelReady';
    case LaravelPerfect = 'LaravelPerfect';
}
