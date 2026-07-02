<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

enum Tag: string
{
    case Legacy = 'legacy-code';
    case LaravelReady = 'laravel-ready';

    public static function tryFromDocComment(string $docComment): ?self
    {
        return Arr::first(
            self::cases(),
            fn (self $tag): bool => Str::contains($docComment, '@'.$tag->value),
        );
    }
}
