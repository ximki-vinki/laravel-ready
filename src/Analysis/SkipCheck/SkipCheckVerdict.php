<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

enum SkipCheckVerdict
{
    case Absent;
    case Active;
    case Expired;
    case Bare;
    case Malformed;
}
