<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

final class SkipCheckEvaluator
{
    public function applies(?string $date, Carbon $today): bool
    {
        if ($date === null) {
            return false;
        }

        try {
            $expires = Carbon::parse($date);
        } catch (InvalidFormatException) {
            return false;
        }

        return $expires->gte($today->copy()->startOfDay());
    }
}
