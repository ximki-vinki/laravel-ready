<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\GlobalFinding;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\UseFinding;

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
