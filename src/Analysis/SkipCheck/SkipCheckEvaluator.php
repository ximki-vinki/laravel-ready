<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

final readonly class SkipCheckEvaluator
{
    public function __construct(private Carbon $today = new Carbon) {}

    public function applies(?SkipCheckParseResult $skipCheck): bool
    {
        $date = $skipCheck?->date; // @pest-mutate-ignore: RemoveNullSafeOperator

        if ($date === null) {
            return false;
        }

        try {
            $expires = Carbon::parse($date);
        } catch (InvalidFormatException) {
            return false;
        }

        return $expires->gte($this->today->copy()->startOfDay());
    }
}
