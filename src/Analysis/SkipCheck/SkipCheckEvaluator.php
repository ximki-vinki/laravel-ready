<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

final readonly class SkipCheckEvaluator
{
    public function __construct(private Carbon $today = new Carbon) {}

    public function verdict(?SkipCheckParseResult $skipCheck): SkipCheckVerdict
    {
        if (! $skipCheck instanceof SkipCheckParseResult) {
            return SkipCheckVerdict::Absent;
        }

        if ($skipCheck->date === null) {
            return SkipCheckVerdict::Bare;
        }

        try {
            $expires = Carbon::parse($skipCheck->date);
        } catch (InvalidFormatException) {
            return SkipCheckVerdict::Malformed;
        }

        return $expires->gte($this->today->copy()->startOfDay())
            ? SkipCheckVerdict::Active
            : SkipCheckVerdict::Expired;
    }

    public function applies(?SkipCheckParseResult $skipCheck): bool
    {
        return $this->verdict($skipCheck) === SkipCheckVerdict::Active;
    }
}
