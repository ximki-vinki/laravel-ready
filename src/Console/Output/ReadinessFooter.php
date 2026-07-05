<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

enum ReadinessFooter: string
{
    case GuardFailed = 'Guard failed: @laravel-ready file must stay LaravelReady.';
    case AdapterFailed = 'Guard failed: @laravel-adapter file must stay LaravelAdapter.';
    case MultiTagFailed = 'MultiTag failed: file must have only one tag.';
    case NotGuarded = 'Not guarded: file has no tag.';
}
