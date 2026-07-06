<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\FunctionCallFinding;
use LaravelReady\Analysis\Findings\GlobalFinding;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\UseFinding;

enum FindingSectionLabel: string
{
    case Var = 'var';
    case Global = 'global';
    case Func = 'func';
    case Use = 'use';

    /**
     * @return class-string<Finding>
     */
    public function findingClass(): string
    {
        return match ($this) {
            self::Var => SuperglobalFinding::class,
            self::Global => GlobalFinding::class,
            self::Func => FunctionCallFinding::class,
            self::Use => UseFinding::class,
        };
    }

    /**
     * @return list<self>
     */
    public static function legacy(): array
    {
        return [
            self::Var,
            self::Global,
            self::Func,
            self::Use,
        ];
    }
}
