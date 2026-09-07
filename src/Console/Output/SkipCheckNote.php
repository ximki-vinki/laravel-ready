<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Displayable;
use LaravelReady\Analysis\SkipCheck\SkipCheckVerdict;

enum SkipCheckNote: string implements Displayable
{
    case Expired = 'expired';
    case Bare = 'missing date';
    case Malformed = 'malformed';

    public static function fromVerdict(SkipCheckVerdict $verdict): ?self
    {
        return match ($verdict) {
            SkipCheckVerdict::Expired => self::Expired,
            SkipCheckVerdict::Bare => self::Bare,
            SkipCheckVerdict::Malformed => self::Malformed,
            SkipCheckVerdict::Absent, SkipCheckVerdict::Active => null,
        };
    }

    public function display(): string
    {
        return $this->value;
    }
}
