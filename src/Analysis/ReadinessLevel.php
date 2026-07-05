<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

enum ReadinessLevel: string
{
    case Untagged = 'Untagged';
    case MultiTag = 'MultiTag';
    case Legacy = 'Legacy';
    case LegacyPerfect = 'LegacyPerfect';
    case LaravelAdapter = 'LaravelAdapter';
    case LaravelReady = 'LaravelReady';
    case LaravelPerfect = 'LaravelPerfect';
}
