<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

enum ReadinessFooter: string
{
    case GuardFailed = 'Guard failed: @laravel-ready file must stay LaravelReady.';
    case MultiTagFailed = 'MultiTag failed: file must have only one tag.';
    case NotGuarded = 'Not guarded: file has no tag.';
}
