<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Enums;

enum DocModifier: string
{
    case Allows = '@allows';
    case SkipCheck = '@skipCheck';
}
