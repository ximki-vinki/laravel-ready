<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum Tag: string
{
    case Legacy = '@legacy-code';
    case LegacyAdapter = '@legacy-adapter';
    case LegacyPerfect = '@legacy-perfect';
    case LaravelAdapter = '@laravel-adapter';
    case LaravelReady = '@laravel-ready';

    /**
     * @return Collection<int, self>
     */
    public static function allFromDocComment(string $docComment): Collection
    {
        return collect(self::cases())
            ->filter(fn (self $tag): bool => Str::contains($docComment, $tag->value))
            ->values();
    }
}
